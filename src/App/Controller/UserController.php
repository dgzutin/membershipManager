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

    }

    public function usersAction(ServerRequestInterface $request, ResponseInterface $response, $args) {


    }

    public function homeAction(ServerRequestInterface $request, ResponseInterface $response) {

        $links = array('home' =>  $request->getUri()->withPath($this->container->router->pathFor('homeUser')),
                       'viewProfile' => $request->getUri()->withPath($this->container->router->pathFor('userProfile')));

        $userService = $this->container->get('userServices');
        $resp = $userService->getUserById($_SESSION['user_id']);
        $user = $resp['user'];
        $userName = $user->getFirstName().' '.$user->getLastName();

        return $this->container->view->render($response, 'user/userHome.html.twig', array(
            'links' => $links,
            'user_id' => $_SESSION['user_id'],
            'user_role' => $_SESSION['user_role'],
            'userName' => $userName
        ));
    }
    
    public function viewUserProfileAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $userId = $_SESSION['user_id'];
        $userService = $this->container->get('userServices');
        $resp = $userService->getUserById($userId);

        //convert the data to be shown in the form
        foreach ($resp['user'] as $key =>$data){
            $user[$key] = array('value' => $data,
                'error' => false);
        }
        $validation = array('exception' => false,
                            'message' => '',
                            'fields' => array());

        $links = array('home' =>  $request->getUri()->withPath($this->container->router->pathFor('homeUser')),
            'viewProfile' => $request->getUri()->withPath($this->container->router->pathFor('userProfile')));

        return $this->container->view->render($response, 'user/userEditProfile.html.twig', array('links' => $links,
                                                                                                 'user_role' => $_SESSION['user_role'],
                                                                                                 'form_submission' => false,
                                                                                                 'exception' => $resp['exception'],
                                                                                                 'message' => $resp['message'],
                                                                                                 'form' => $user));
    }

    public function saveUserProfileAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $links = array('home' =>  $request->getUri()->withPath($this->container->router->pathFor('homeUser')),
                       'viewProfile' => $request->getUri()->withPath($this->container->router->pathFor('userProfile')));

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

            return $this->container->view->render($response, 'user/userEditProfile.html.twig', array('links' => $links,
                                                                                                     'user_role' => $_SESSION['user_role'],
                                                                                                     'form_submission' => true,
                                                                                                     'exception' => $form_validation['exception'],
                                                                                                     'message' => $form_validation['message'],
                                                                                                     'form' => $val_array));
        }
        else{
            $userId = $_SESSION['user_id'];

            $userService = $this->container->get('userServices');
            $resp = $userService->updateUserProfile($userId, $form_data);

            //convert the data to be shown in the form
            foreach ($resp['user'] as $key =>$data){
                $user[$key] = array('value' => $data,
                    'error' => false);
            }

            return $this->container->view->render($response, 'user/userEditProfile.html.twig', array('links' => $links,
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

        $userService = $this->container->get('userServices');
        $resp = $userService->getUserById($userId);

        $mailServices = $this->container->get('mailServices');
        $resetResp = $mailServices->sendResetPasswordMail($resp['user'], $request);

        return $this->container->view->render($response, 'userNotification.twig', $resetResp);
    }

    public function yourMembershipAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $em = $this->container->get('em');
        $repository = $em->getRepository('App\Entity\MembershipType');

        $shoppingCartServices = $this->container->get('shoppingCartServices');
        $shoppingCartServices->emptyCart();

        if ($_SESSION['user_role'] == 'ROLE_ADMIN'){

            $membershipTypes = $repository->createQueryBuilder('memberships')
                ->select('memberships')
                ->getQuery()
                ->getResult();
        }
        else{

            $membershipTypes = $repository->createQueryBuilder('memberships')
                ->select('memberships')
                ->where('memberships.selectable = :selectable')
                ->setParameter('selectable', true)
                ->getQuery()
                ->getResult();
        }


        return $this->container->view->render($response, 'user/selectMembershipUser.html.twig', array(
            'user_role' => $_SESSION['user_role'],
            'membershipTypes' => $membershipTypes,
            'checkoutUrl' => $request->getUri()->withPath($this->container->router->pathFor('orderSummaryUser')),
            'addMembershipToCartUrl' => $request->getUri()->getBaseUrl(). '/user/addMembershipToCart',
            'currency' => 'EUR',
            'removeItemBaseUrl' => $request->getUri()->getBaseUrl(). '/user/removeItemfromCart',
            'message' => '',
            'form' => ''));
    }


    public function orderSummaryAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        //retrive user data for billing information
        $userId = $_SESSION['user_id'];
        $userService = $this->container->get('userServices');
        $userResp = $userService->getUserById($userId);
        $user =  $userResp['user'];

        $membershipServices = $this->container->get('membershipServices');

        // shoppingCartServices
        $shoppingCartServices = $this->container->get('shoppingCartServices');
        $itemsResp = $shoppingCartServices->getItems();

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

        $totalPrice = $shoppingCartServices->getTotalPrice($itemsResp['items']);
        $totalPrice = $shoppingCartServices->convertAmountToLocaleSettings($totalPrice);
        $items = $shoppingCartServices->convertAmountsToLocaleSettings($itemsResp['items']);

        if (count($items) == 0){

            // If shopping cart is empty return to home
            $uri = $request->getUri()->withPath($this->container->router->pathFor('homeUser'));
            return $response = $response->withRedirect($uri, 200);

        }

        return $this->container->view->render($response, 'user/oderSummary.twig', array(
            'user_role' => $_SESSION['user_role'],
            'exception' => false,
            'items' => $items,
            'currency' => 'EUR',
            'totalPrice' => $totalPrice,
            'removeItemBaseUrl' => $request->getUri()->getBaseUrl(). '/user/removeItemfromCart',
            'message' => '',
            'form' => $billing));

    }

    public function confirmOrderAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        //retrive user data for billing information
        $userId = $_SESSION['user_id'];
        $userService = $this->container->get('userServices');
        $userResp = $userService->getUserById($userId);
        $user =  $userResp['user'];

        $membershipServices = $this->container->get('membershipServices');

        // shoppingCartServices
        $shoppingCartServices = $this->container->get('shoppingCartServices');
        $itemsResp = $shoppingCartServices->getItems();

        //Try to retrieve the billing address from post valiables
        $form_data = $request->getParsedBody();

        $totalPrice = $shoppingCartServices->getTotalPrice($itemsResp['items']);
        $totalPrice = $shoppingCartServices->convertAmountToLocaleSettings($totalPrice);
        $items = $shoppingCartServices->convertAmountsToLocaleSettings($itemsResp['items']);

        return $this->container->view->render($response, 'user/confirmOrder.html.twig', array(
            'user_role' => $_SESSION['user_role'],
            'exception' => false,
            'items' => $items,
            'currency' => 'EUR',
            'totalPrice' => $totalPrice,
            'removeItemBaseUrl' => $request->getUri()->getBaseUrl(). '/user/removeItemfromCart',
            'message' => '',
            'terms' => 'Payment is due within 30 days after confirmation of purchase. Form membership fee payments, the membership will only be be active after the full payment is received.',
            'form' => $form_data));

    }


    public function processOrderAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $userId = $_SESSION['user_id'];
        $userService = $this->container->get('userServices');
        $userResp = $userService->getUserById($userId);
        $user =  $userResp['user'];

        $form_data = $request->getParsedBody();

        $billingInfo = new Billing();

        $billingInfo->setName($form_data['name']);
        $billingInfo->setInstitution($form_data['institution']);
        $billingInfo->setStreet($form_data['street']);
        $billingInfo->setCountry($form_data['country']);
        $billingInfo->setCity($form_data['city']);
        $billingInfo->setZip($form_data['zip']);
        $billingInfo->setVat($form_data['vat']);
        $billingInfo->setReference($form_data['reference']);

        // shoppingCartServices
        $shoppingCartServices = $this->container->get('shoppingCartServices');
        $itemsResp = $shoppingCartServices->getItems();


        $resultsGenInvoice = $userService->generateInvoiceForUser($user, $billingInfo, $itemsResp['items']);

        if ($resultsGenInvoice['exception'] == false){


            //delete shopping cart items if invoice is generated
            $shoppingCartServices->emptyCart();

            $uri = $request->getUri()->withPath($this->container->router->pathFor('singleInvoice',[
                'invoiceId' => $resultsGenInvoice['invoiceId']]));

            return $response = $response->withRedirect($uri, 200);

        }
    }

    public function singleInvoiceAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $userId = $_SESSION['user_id'];
        $userService = $this->container->get('userServices');
        $invoiceId = $args['invoiceId'];
        //Retrieve the user creating the invoice
        $userResp = $userService->getUserById($userId);

        if ($userResp['exception'] == false){
            $user =  $userResp['user'];
        }
        else{
            return $this->container->view->render($response, 'userNotification.twig', $userResp);
        }

        $respInvoiceData = $userService->getInvoiceDataForUser($invoiceId, $userId);

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
            'user' => $user,
            'exception' => $respInvoiceData['exception'],
            'invoiceData' => $respInvoiceData['invoice'],
            'invoiceDate' => $respInvoiceData['invoiceDate'],
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


    public function registerMemberAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        //TODO: verify if user is already a member

        $userId = $_SESSION['user_id'];
        $userService = $this->container->get('userServices');
        $resp = $userService->getUserById($userId);

        if ($resp['exception'] == false){

            $links = array('home' =>  $request->getUri()->withPath($this->container->router->pathFor('homeUser')),
                'viewProfile' => $request->getUri()->withPath($this->container->router->pathFor('userProfile')));

            return $this->container->view->render($response, 'user/registerMember.html.twig', array('links' => $links,
                'user_role' => $_SESSION['user_role'],
                'membershipSelected' => $args['selected'],
                'user' => $resp['user'],
                'form' => ''));
        }
        else{
            return $this->container->view->render($response, 'userNotification.twig', $resp);
        }
    }
    
}