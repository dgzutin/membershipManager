<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 19.05.16
 * Time: 13:01
 */
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use App\Entity\Billing;
use App\Entity\ShoppingCartItem;


class UserController {
    protected $container;
    //Constructor
    public function __construct($container) {

        $this->container = $container;
        $this->em = $container['em'];
        $this->userServices = $container->get('userServices');
        $this->membershipServices = $container->get('membershipServices');
        $this->shoppingCartServices = $container->get('shoppingCartServices');
        $this->mailServices = $container->get('mailServices');
        $this->systemInfo = $this->userServices->getSystemInfo();

    }

    public function usersAction(ServerRequestInterface $request, ResponseInterface $response, $args) {


    }

    public function homeAction(ServerRequestInterface $request, ResponseInterface $response) {


        $resp = $this->userServices->getUserById($_SESSION['user_id']);
        $user = $resp['user'];
        $userName = $user->getFirstName().' '.$user->getLastName();

        $memberships = $this->membershipServices->getMembershipsForUser($user->getId());

        $openInvoiceResult = $this->userServices->getOpenInvoicesForUser($_SESSION['user_id']);

        if ($this->systemInfo['exception'] == true){

            $systemInfo = null;
        }
        return $this->container->view->render($response, 'user/userHome.html.twig', array(
            'invoiceInfo' => $openInvoiceResult,
            'user_id' => $_SESSION['user_id'],
            'user_role' => $_SESSION['user_role'],
            'userName' => $userName,
            'memberships' => $memberships,
            'systemInfo' => $this->systemInfo
        ));
    }
    
    public function viewUserProfileAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $userId = $_SESSION['user_id'];
        $resp = $this->userServices->getUserById($userId);

        //convert the data to be shown in the form
        foreach ($resp['user'] as $key =>$data){
            $user[$key] = array('value' => $data,
                'error' => false);
        }
        $validation = array('exception' => false,
                            'message' => '',
                            'fields' => array());


        return $this->container->view->render($response, 'user/userEditProfile.html.twig', array('systemInfo' => $this->systemInfo,
                                                                                                 'user_role' => $_SESSION['user_role'],
                                                                                                 'form_submission' => false,
                                                                                                 'exception' => $resp['exception'],
                                                                                                 'message' => $resp['message'],
                                                                                                 'form' => $user));
    }

    public function saveUserProfileAction(ServerRequestInterface $request, ResponseInterface $response)
    {
       $form_data = $request->getParsedBody();

        foreach ($form_data as $key =>$data){

            //add validation code for each input here
            $val_array[$key] = array('value' => $data,
                                     'error' => false);
        }

        $form_validation = array('exception' => false,
                                 'message' => 'One or more fields are not valid. Please check your data and submit it again.',
                                 'fields' => array());


        if ($form_validation['exception'] == true){

            return $this->container->view->render($response, 'user/userEditProfile.html.twig', array('systemInfo' => $this->systemInfo,
                                                                                                     'user_role' => $_SESSION['user_role'],
                                                                                                     'form_submission' => true,
                                                                                                     'exception' => $form_validation['exception'],
                                                                                                     'message' => $form_validation['message'],
                                                                                                     'form' => $val_array));
        }
        else{
            $userId = $_SESSION['user_id'];

            $resp = $this->userServices->updateUserProfile($userId, $form_data);

            //convert the data to be shown in the form
            foreach ($resp['user'] as $key =>$data){
                $user[$key] = array('value' => $data,
                    'error' => false);
            }

            return $this->container->view->render($response, 'user/userEditProfile.html.twig', array('systemInfo' => $this->systemInfo,
                                                                                                     'user_role' => $_SESSION['user_role'],
                                                                                                     'form_submission' => true,
                                                                                                     'exception' => $resp['exception'],
                                                                                                     'message' => $resp['message'],
                                                                                                     'form' => $user));
        }
    }

    public function resetPasswordAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $userId = $_SESSION['user_id'];

        $resp = $this->userServices->getUserById($userId);

        $resetResp = $this->mailServices->sendResetPasswordMail($resp['user'], $request);

        return $this->container->view->render($response, 'userNotificationMail.twig',  array('systemInfo' => $this->systemInfo,
                                                                                             'mailResponse' => $resetResp));
    }

    public function yourMembershipAction(ServerRequestInterface $request, ResponseInterface $response)
    {

        $this->shoppingCartServices->emptyCart();
        $user = $this->userServices->getUserById($_SESSION['user_id']);
        $result = $this->membershipServices->getMembershipTypeAndStatusOfUser($user['user'], NULL, false);
        
        if ($result['exception'] == true){
            return $this->container->view->render($response, 'userNotification.twig', $result);            
        }

        return $this->container->view->render($response, 'user/selectMembershipUser.html.twig', array(
            'systemInfo' => $this->systemInfo,
            'user_role' => $_SESSION['user_role'],
            'membershipTypes' => $result['membershipTypes'],
            'checkoutUrl' => $request->getUri()->withPath($this->container->router->pathFor('orderSummaryUser')),
            'addMembershipToCartUrl' => $request->getUri()->getBaseUrl(). '/user/addMembershipToCart',
            'currency' => 'EUR',
            'removeItemBaseUrl' => $request->getUri()->getBaseUrl(). '/user/removeItemfromCart',
            'message' => $result['message'],
            'form' => ''));
    }


    public function orderSummaryAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        //retrive user data for billing information
        $userId = $_SESSION['user_id'];
        $userResp = $this->userServices->getUserById($userId);
        $user =  $userResp['user'];

        $membershipServices = $this->container->get('membershipServices');

        // shoppingCartServices
        $shoppingCartServices = $this->container->get('shoppingCartServices');
        $itemsResp = $shoppingCartServices->getItems();
        
        //get Billing Info
        $billingInfo = $this->userServices->getBillingInfoForUser($userId);

        //Try to retrieve the billing address if renewing membership
        //TODO: check if is renewal or not
        $renewing = false;
        
        if ($renewing == true){

            $billingAddressresp = $membershipServices->getBillingInfoForMembership(NULL);

            //convert the data to be shown in the form
            foreach ($billingAddressresp['billingAddress'] as $key =>$data){
                    $billing[$key] = array('value' => $data,
                                           'error' => false);
            }
        }
        else{

            if ($billingInfo['exception'] == true){

                //convert the data to be shown in the form
                $billing['name'] = array('value' => $user->title.' '.$user->first_name.' '.$user->last_name,
                    'error' => false);
                $billing['institution'] = array('value' => $user->institution,
                    'error' => false);
                $billing['street'] = array('value' => $user->street,
                    'error' => false);
                $billing['country'] = array('value' => $user->country,
                    'error' => false);
                $billing['city'] = array('value' => $user->city,
                    'error' => false);
                $billing['zip'] = array('value' => $user->zip,
                    'error' => false);
            }
            else{
                //convert the data to be shown in the form
                foreach ($billingInfo['billing'] as $key =>$data){
                         $billing[$key] = array('value' => $data,
                                                'error' => false);
                }
            }
        }

        $totalPrice = $shoppingCartServices->getTotalPrice($itemsResp['items']);
        $totalPrice = $shoppingCartServices->convertAmountToLocaleSettings($totalPrice);
        $items = $shoppingCartServices->convertAmountsToLocaleSettings($itemsResp['items']);

        if (count($items) == 0){

            // If shopping cart is empty return to home
            $uri = $request->getUri()->withPath($this->container->router->pathFor('homeUser'));
            return $response = $response->withRedirect($uri, 200);
        }

        return $this->container->view->render($response, 'user/oderSummary.twig', array(
            'systemInfo' => $this->systemInfo,
            'user_role' => $_SESSION['user_role'],
            'exception' => false,
            'items' => $items,
            'totalPrice' => $totalPrice,
            'removeItemBaseUrl' => $request->getUri()->getBaseUrl(). '/user/removeItemfromCart',
            'message' => '',
            'form' => $billing));

    }

    public function confirmOrderAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        //retrive user data for billing information
        $userId = $_SESSION['user_id'];
        $userResp = $this->userServices->getUserById($userId);
        $user =  $userResp['user'];

        // shoppingCartServices
        $shoppingCartServices = $this->container->get('shoppingCartServices');
        $itemsResp = $shoppingCartServices->getItems();

        //Try to retrieve the billing address from post valiables
        $form_data = $request->getParsedBody();

        $totalPrice = $shoppingCartServices->getTotalPrice($itemsResp['items']);
        $totalPrice = $shoppingCartServices->convertAmountToLocaleSettings($totalPrice);
        $items = $shoppingCartServices->convertAmountsToLocaleSettings($itemsResp['items']);

        return $this->container->view->render($response, 'user/confirmOrder.html.twig', array(
            'systemInfo' => $this->systemInfo,
            'user_role' => $_SESSION['user_role'],
            'exception' => false,
            'items' => $items,
            'currency' => 'EUR',
            'totalPrice' => $totalPrice,
            'removeItemBaseUrl' => $request->getUri()->getBaseUrl(). '/user/removeItemfromCart',
            'message' => '',
            'invoiceTerms' => 'Payment is due within 30 days after confirmation of purchase. For membership fee payments, the membership will only be be active after the full payment is received.',
            'form' => $form_data));

    }


    public function processMembershipOrderAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $userId = $_SESSION['user_id'];
        $userResp = $this->userServices->getUserById($userId);
        $user =  $userResp['user'];

        $form_data = $request->getParsedBody();
        
        //Create or update billing info. 
        $billingResp = $this->userServices->createOrUpdateBillingInfo($form_data, $user);

        // shoppingCartServices
        $shoppingCartServices = $this->container->get('shoppingCartServices');

        if ($billingResp['exception'] == true){
            return $this->container->view->render($response, 'userNotification.twig', $billingResp);
        }

        $itemsResp = $shoppingCartServices->getItems();

        if ($itemsResp['exception'] == true){
            return $this->container->view->render($response, 'userNotification.twig', $itemsResp);
        }

        foreach ($itemsResp['items'] as $item){

            //add Membership to the database and link it to user. Active is set to false
            $addMemberResult = $this->membershipServices->addMember($userId, NULL, $item->getTypeId());

            if ($addMemberResult['exception'] == true){
                return $this->container->view->render($response, 'userNotification.twig', array('exception' => true,
                                                                                                'systemInfo' => $this->systemInfo,
                                                                                                'message' => $addMemberResult['message']));
            }
        }
        //Generate invoice if member is succesfully added
        $resultsGenInvoice = $this->userServices->generateInvoiceForUser($user, $billingResp['billingInfo'], $itemsResp['items'], NULL);

        if ($resultsGenInvoice['exception'] == false){

            //delete shopping cart items if invoice is generated
            $shoppingCartServices->emptyCart();

            return $this->container->view->render($response, '/user/confirmationAndPayment.html.twig', array('systemInfo' => $this->systemInfo,
                                                                                                              'resultsGenInvoice' => $resultsGenInvoice));
        }

        else{
            return $this->container->view->render($response, 'userNotification.twig',  array('exception' => true,
                                                                                             'systemInfo' => $this->systemInfo,
                                                                                             'message' => $resultsGenInvoice['message']));
        }
    }

    public function singleInvoiceAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $userId = $_SESSION['user_id'];
        $invoiceId = $args['invoiceId'];
        //Retrieve the user creating the invoice
        $userResp = $this->userServices->getUserById($userId);

        if ($userResp['exception'] == false){
            $user =  $userResp['user'];
        }
        else{
            return $this->container->view->render($response, 'userNotification.twig', $userResp);
        }

        $respInvoiceData = $this->userServices->getInvoiceDataForUser($invoiceId, $userId);

        if ($respInvoiceData['exception'] == true){

            return $this->container->view->render($response, 'userNotification.twig', $respInvoiceData);
        }

        // Convert all prices to locale settings ---------------------
        $shoppingCartServices = $this->container->get('shoppingCartServices');
        $totalPrice_formatted = $shoppingCartServices->convertAmountToLocaleSettings($respInvoiceData['totalPrice']);
        $amountPaid_formatted = $shoppingCartServices->convertAmountToLocaleSettings($respInvoiceData['amountPaid']);
        $outstandingAmount_formatted = $shoppingCartServices->convertAmountToLocaleSettings($respInvoiceData['outstandingAmount']);
        $items = $respInvoiceData['invoiceItems'];

        $i = 0;
        foreach ($items as $item){
            $items[$i]->setUnitPrice($shoppingCartServices->convertAmountToLocaleSettings($item->getUnitPrice()));
            $items[$i]->setTotalPrice($shoppingCartServices->convertAmountToLocaleSettings($item->getTotalPrice()));
            $i++;
        }

        // END Convert all prices to locale settings ---------------------
        return $this->container->view->render($response, 'user/singleInvoice.html.twig', array('user_role' => $_SESSION['user_role'],
            'systemInfo' => $this->systemInfo,
            'user' => $user,
            'exception' => $respInvoiceData['exception'],
            'invoiceData' => $respInvoiceData['invoice'],
            'invoiceDate' => $respInvoiceData['invoiceDate'],
            'invoiceDueDate' => $respInvoiceData['invoiceDueDate'],
            'items' => $respInvoiceData['invoiceItems'],
            'issuerData' => $respInvoiceData['issuerData'],
            'totalPrice' =>  $totalPrice_formatted,
            'amountPaid' => $amountPaid_formatted,
            'outstandingAmount' => $outstandingAmount_formatted,
            'outstandingAmount_paypal' => $respInvoiceData['outstandingAmount'], //original US locale to be passed to paypal.
            'removeItemBaseUrl' => $request->getUri()->getBaseUrl(). '/user/removeItemfromCart',
            'message' => $respInvoiceData['message']));

    }

    public function removeItemfromCartAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $userId = $_SESSION['user_id'];
        $itemId = $args['itemId'];
        $shoppingCartServices = $this->container->get('shoppingCartServices');
        $resp = $shoppingCartServices->removeItemFromCart($userId, $itemId, NULL);

        if ($resp['exception'] == true){

            return $this->container->view->render($response, 'userNotification.twig', $resp);
        }
        $uri = $request->getUri()->withPath($this->container->router->pathFor('yourMembershipUser'));
        return $response = $response->withRedirect($uri, 200);

    }

    public function addMembershipToCartAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $membershipTypeId = $args['membershipTypeId'];
        $shoppingCartServices = $this->container->get('shoppingCartServices');
        $res = $shoppingCartServices->addIMembershipToCart($membershipTypeId);

        $uri = $request->getUri()->withPath($this->container->router->pathFor('orderSummaryUser'));
        return $response = $response->withRedirect($uri, 200);
    }


    public function membershipDataAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $membershipTypeId = $args['membershipTypeId'];

        //TODO: verify if user is already a member

        $resp = $this->userServices->getUserById($_SESSION['user_id']);

        if ($resp['exception'] == false){

            $result = $this->membershipServices->getMembershipTypeAndStatusOfUser($resp['user'], $membershipTypeId, false);

            if ($result['exception'] == true){
                return $this->container->view->render($response, 'userNotification.twig', $result);
            }

            return $this->container->view->render($response, 'user/membershipData.html.twig', array(
                'systemInfo' => $this->systemInfo,
                'user' => $resp['user'],
                'user_role' => $_SESSION['user_role'],
                'membershipTypes' => $result['membershipTypes'],
                'removeItemBaseUrl' => $request->getUri()->getBaseUrl(). '/user/removeItemfromCart',
                'message' => '',
                'form' => ''));
        }
        else{
            return $this->container->view->render($response, 'userNotification.twig', $resp);
        }
    }

    public function testAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $userId = $_SESSION['user_id'];

        //$user = $this->userServices->getUserById($userId);

        //$validUntil = new \DateTime('2018-12-31 23:59:59');
        //$result = $this->membershipServices->addNewMembershipValidity(58, 'year', '10-30', NULL, NULL);
        //$Result = $this->membershipServices->getMembershipTypeAndStatusOfUser($user['user'], NULL, true);

        //$result = $this->membershipServices->getMembershipValidity(58);
        //$Result = $this->userServices->getInvoiceDataForUser(135, 2);

        /*
        $em = $this->container->get('em');
        $repository = $em->getRepository('App\Entity\MembershipValidity');
            $maxValidity = $repository->createQueryBuilder('Validity')
                ->select('MAX(Validity.validUntil)')
                ->where('Validity.membershipId = :membershipId')
                ->setParameter('membershipId', 58)
                ->getQuery()
                ->getSingleScalarResult();

        $validity = $repository->createQueryBuilder('Validity')
            ->select('Validity')
            ->where('Validity.validUntil = :validUntil')
            ->andWhere('Validity.membershipId = :membershipId')
            ->setParameter('validUntil', $maxValidity)
            ->setParameter('membershipId', 58)
            ->getQuery()
            ->getResult();
*/

        //$result = $this->membershipServices->getMembers(array());

        $result = $this->membershipServices->getMembershipsForUser($userId);

        //var_dump(max(array_keys($validity)));

        var_dump($result);
       // echo 'ok';
    }
    
}