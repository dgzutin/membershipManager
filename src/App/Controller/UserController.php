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
use \Httpful\Request;
use \Exception;
use App\Entity\User;
use TCPDF;


class UserController {
    protected $container;
    //Constructor
    public function __construct($container) {

        $this->container = $container;
        $this->em = $container['em'];
        $this->systemInfo = $container->get('userServices')->getSystemInfo();
        $this->userServices = $container->get('userServices');
        $this->utilsServices = $container->get('utilsServices');
        $this->membershipServices = $container->get('membershipServices');
        $this->shoppingCartServices = $container->get('shoppingCartServices');
        $this->mailServices = $container->get('mailServices');
        $this->siteManagementServices = $container->get('siteManagementServices');
        $this->billingServices = $container->get('billingServices');
        $this->pdfGenerationServices = $container->get('pdfGenerationServices');

        //$this->systemInfo = $this->userServices->getSystemInfo();

    }
    
    public function homeAction(ServerRequestInterface $request, ResponseInterface $response)
    {

        $resp = $this->userServices->getUserById($_SESSION['user_id']);
        $user = $resp['user'];
        $userName = $user->getFirstName().' '.$user->getLastName();

        $memberships = $this->membershipServices->getMembershipsForUser($user->getId());
        $openInvoiceResult = $this->userServices->getInvoices($_SESSION['user_id']);

        //get membership types
        $this->shoppingCartServices->emptyCart((int)$_SESSION['user_id']);
        $membResult = $this->membershipServices->getMembershipTypeAndStatusOfUser($user, NULL, false);


        return $this->container->view->render($response, 'user/userHome.html.twig', array(
            'invoiceInfo' => $openInvoiceResult,
            'userName' => $userName,
            'memberships' => $memberships,
            'pictureUrl' => $user->getPictureUrl(),
            'membershipTypes' => $membResult['membershipTypes'],
            'currency' => 'EUR',
            'message' => $membResult['message'],
            'form' => ''
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

        if (isset($_SESSION['linkedInStatus'])){
            $linkedInStatus = $_SESSION['linkedInStatus'];
            unset($_SESSION['linkedInStatus']);
        }


        return $this->container->view->render($response, 'user/userEditProfile.html.twig', array(
            'form_submission' => false,
            'exception' => $resp['exception'],
            'message' => $resp['message'],
            'linkedInOauthEndpoint' => 'https://www.linkedin.com/oauth/v2/authorization',
            'oauthRedirect' => $this->utilsServices->getUrlForRouteName($request, 'linkedInOauth2Redirect'),
            'linkedInState' => 'associate',
            'linkedIn' => $linkedInStatus,
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

            return $this->container->view->render($response, 'user/userEditProfile.html.twig', array(
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

            return $this->container->view->render($response, 'user/userEditProfile.html.twig', array(
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

        return $this->container->view->render($response, 'userNotificationMail.twig',  array('mailResponse' => $resetResp));
    }

    public function selectMembershipAction(ServerRequestInterface $request, ResponseInterface $response)
    {

        $this->shoppingCartServices->emptyCart((int)$_SESSION['user_id']);
        $user = $this->userServices->getUserById($_SESSION['user_id']);
        $result = $this->membershipServices->getMembershipTypeAndStatusOfUser($user['user'], NULL, false);

        if ($result['exception'] == true){
            return $this->container->view->render($response, 'userNotification.twig', $result);            
        }

        return $this->container->view->render($response, 'user/selectMembershipUser.html.twig', array(
            'membershipTypes' => $result['membershipTypes'],
            'currency' => 'EUR',
            'message' => $result['message'],
            'form' => ''));
    }


    public function membershipOrderSummaryAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        
        //retrive user data for billing information
        $userId = $_SESSION['user_id'];
        $userResp = $this->userServices->getUserById($userId);
        $user =  $userResp['user'];

        // shoppingCartServices
        $shoppingCartServices = $this->container->get('shoppingCartServices');
        $itemsResp = $shoppingCartServices->getItems();
        
        //get Billing Info
        $billingInfo = $this->userServices->getBillingInfoForUser($userId);

        if ($billingInfo['exception']){

            //convert the data to be shown in the form
            $billing['name'] = $user->title.' '.$user->first_name.' '.$user->last_name;
            $billing['institution'] = $user->institution;
            $billing['street'] = $user->street;
            $billing['country'] = $user->country;
            $billing['city'] = $user->city;
            $billing['zip'] = $user->zip;
        }
        else{
            $billing = $billingInfo['billing'];
        }


        $totalPrice = $shoppingCartServices->getTotalPrice($itemsResp['items']);

        if (count($itemsResp['items']) == 0){

            // If shopping cart is empty return to home
            $uri = $this->utilsServices->getBaseUrl($request).'/user/home';
            return $response = $response->withRedirect($uri, 200);
        }
        
        $membershipType = $this->membershipServices->getMembershipTypeById((int)$args['membershipTypeId']);

        return $this->container->view->render($response, 'user/oderSummary.twig', array(
            'exception' => false,
            'membershipType' => $membershipType['membershipType'],
            'membershipTypeId' => (int)$args['membershipTypeId'],
            'items' => $itemsResp['items'],
            'totalPrice' => $totalPrice,
            'removeItemBaseUrl' => $this->utilsServices->getBaseUrl($request). '/user/removeItemfromCart',
            'message' => '',
            'billingInfo' => $billing));

    }

    public function confirmMembershipOrderAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        //retrive user data for billing information
        $userId = $_SESSION['user_id'];
        $userResp = $this->userServices->getUserById($userId);
        $user =  $userResp['user'];

        // shoppingCartServices
        $shoppingCartServices = $this->container->get('shoppingCartServices');
        $itemsResp = $shoppingCartServices->getItems();

        //Try to retrieve the billing address from post variables and save it
        $form_data = $request->getParsedBody();
        $this->userServices->createOrUpdateBillingInfo($form_data, $user);

        //get the user comments. In the future more fields can be added
        $membershipData['comments'] = $form_data['comments'];

        $totalPrice = $shoppingCartServices->getTotalPrice($itemsResp['items']);
        $totalPrice = $shoppingCartServices->convertAmountToLocaleSettings($totalPrice);
        $items = $shoppingCartServices->convertAmountsToLocaleSettings($itemsResp['items']);

        $systemInfo = $this->userServices->getSystemInfo();

        $membershipType = $this->membershipServices->getMembershipTypeById((int)$args['membershipTypeId']);

        return $this->container->view->render($response, 'user/confirmOrder.html.twig', array(
            'exception' => false,
            'membershipType' => $membershipType['membershipType'],
            'membershipTypeId' => (int)$args['membershipTypeId'],
            'items' => $items,
            'currency' => $systemInfo['settings']->getSystemCurrency(),
            'totalPrice' => $totalPrice,
            'message' => '',
            'membershipData' => $membershipData,
            'invoiceTerms' => 'Payment is due within 30 days after confirmation of purchase. For membership fee payments, the membership will only be be active after the full payment is received.',
            'billingInfo' => $form_data));
    }


    public function processMembershipOrderAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $userId = (int)$_SESSION['user_id'];
        $userResp = $this->userServices->getUserById($userId);
        $user =  $userResp['user'];

        $membershipType = $this->membershipServices->getMembershipTypeById($args['membershipTypeId']);

        if ($membershipType['exception']){
            return $this->container->view->render($response, 'userNotification.twig', $membershipType);
        }

        $form_data = $request->getParsedBody();

        $membershipData['comments'] = $form_data['comments'];
        $membershipData['membershipTypeId'] =  (int)$args['membershipTypeId'];
        $membershipData['cancelled'] = 0;


        //check if membership is free
        if ($membershipType['membershipType']->getFee() > 0){

            //get billing info for user
            $billingInfo = $this->userServices->getBillingInfoForUser($userId);

            // shoppingCartServices
            $shoppingCartServices = $this->container->get('shoppingCartServices');

            if ($billingInfo['exception'] == true){
                return $this->container->view->render($response, 'userNotification.twig', $billingInfo);
            }

            $itemsResp = $shoppingCartServices->getItems();

            if ($itemsResp['exception'] == true){
                return $this->container->view->render($response, 'userNotification.twig', $itemsResp);
            }

            //add Membership to the database and link it to user.
            $addMemberResult = $this->membershipServices->addUpdateMember($userId, NULL, $args['membershipTypeId'], $membershipData);

            if ($addMemberResult['exception'] == true){
                return $this->container->view->render($response, 'userNotification.twig',
                    array('exception' => true,
                          'message' => $addMemberResult['message']));
                }
                $membershipId = $addMemberResult['member']['membership']->getId();

            //Generate invoice if member is successfully added AND create on payment actions

            $onPaymentActions = array(array ('actionName' => 'renewForOnePeriod',
                                             'membershipIds' => array($membershipId)));

            $resultsGenInvoice = $this->userServices->generateUpdateInvoiceForUser(null, $user, $billingInfo['billing'], $itemsResp['items'], json_encode($onPaymentActions), true, $request, $membershipType['membershipType']->getCurrency(), NULL, NULL, NULL);

            if ($resultsGenInvoice['exception'] == false){
                //delete shopping cart items if invoice is generated
                $shoppingCartServices->emptyCart($userId);

                return $this->container->view->render($response, '/user/confirmationAndPayment.html.twig', 
                    array('resultsGenInvoice' => $resultsGenInvoice));
            }

            else{
                return $this->container->view->render($response, 'userNotification.twig',  
                    array('exception' => true,
                          'message' => $resultsGenInvoice['message']));
            }
        }

        //add Membership to the database and link it to user. Active is set to false
        $addMemberResult = $this->membershipServices->addUpdateMember($userId, NULL, $args['membershipTypeId'], $membershipData);

        return $this->container->view->render($response, 'userNotification.twig', $addMemberResult);

    }

    public function singleInvoiceAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $userId = $_SESSION['user_id'];
        $invoiceId = $args['invoiceId'];
        //Retrieve the user creating the invoice
        $userResp = $this->userServices->getUserById($userId);

        $payPalData = null;
        if ($request->isPost()){
            $payPalData = $request->getParsedBody();
        }

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
        

        return $this->container->view->render($response, 'user/singleInvoice.html.twig', array(
            'user' => $user,
            'exception' => $respInvoiceData['exception'],
            'invoiceData' => $respInvoiceData['invoice'],
            'invoiceDate' => $respInvoiceData['invoiceDate'],
            'paidDate' => $respInvoiceData['paidDate'],
            'invoiceDueDate' => $respInvoiceData['invoiceDueDate'],
            'items' => $respInvoiceData['invoiceItems'],
            'issuerData' => $respInvoiceData['issuerData'],
            'totalPrice_nett' =>  $respInvoiceData['totalPrice_nett'],
            'totalTaxes' => $respInvoiceData['totalTaxes'],
            'totalPrice' =>  $respInvoiceData['totalPrice'],
            'amountPaid' => $respInvoiceData['amountPaid'],
            'outstandingAmount' => $respInvoiceData['outstandingAmount'],
            'outstandingAmount_paypal' => $respInvoiceData['outstandingAmount'],
            'paypal_ipn_url' => $this->utilsServices->getBaseUrl($request).'/paypal_ipn',
            'invoiceLink' =>  $this->utilsServices->getBaseUrl($request).'/user/singleInvoice/'.$respInvoiceData['invoice']->getId(),
            'returnUrl' => $this->utilsServices->getBaseUrl($request).'/user/verifyingPayment',
            'message' => $respInvoiceData['message'],
            'payPalData' => $payPalData,
            'isPost' => $request->isPost()));

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
        $uri = $this->utilsServices->getBaseUrl($request).'/user/selectMembership';
        return $response = $response->withRedirect($uri, 200);

    }

    public function addMembershipToCartAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $membershipTypeId = (int)$args['membershipTypeId'];
        $shoppingCartServices = $this->container->get('shoppingCartServices');
        $res = $shoppingCartServices->addIMembershipToCart($membershipTypeId);

        if ($res['exception']){
            return $this->container->view->render($response, 'userNotification.twig', $res);
        }

        $uri = $this->utilsServices->getBaseUrl($request).'/user/membershipOrderSummary/'.$membershipTypeId;
        return $response = $response->withRedirect($uri, 200);
    }


    public function membershipDataAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $membershipTypeId = $args['membershipTypeId'];

        $resp = $this->userServices->getUserById($_SESSION['user_id']);

        if ($resp['exception'] == false){

            $result = $this->membershipServices->getMembershipTypeAndStatusOfUser($resp['user'], $membershipTypeId, false);

            if ($result['exception'] == true){
                return $this->container->view->render($response, 'userNotification.twig', $result);
            }

            return $this->container->view->render($response, 'user/membershipData.html.twig', array(
                'user' => $resp['user'],
                'membershipTypes' => $result['membershipTypes'],
                'removeItemBaseUrl' => $this->utilsServices->getBaseUrl($request).'/user/removeItemfromCart',
                'message' => '',
                'form' => ''));
        }
        else{
            return $this->container->view->render($response, 'userNotification.twig', $resp);
        }
    }

    public function manageMembershipAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $memberId = (int)$args['memberId'];

        $form_submission = false;
        if ($request->isPost()){

            $form_data = $request->getParsedBody();
            $membershipData['comments'] = $form_data['comments'];
            $membershipData['membershipTypeId'] = (int)$form_data['membershipTypeId'];
            $form_submission = true;
            $updateMemberResult = $this->membershipServices->addUpdateMember($_SESSION['user_id'], NULL, $membershipData['membershipTypeId'], $membershipData);

        }

        $result = $this->membershipServices->getMemberByMemberId($memberId);

        if ($result['exception']){

            return $this->container->view->render($response, 'userNotification.twig', $result);
        }

        if ($form_submission){
            $result['form_submission'] = true;
            $result['message'] = $updateMemberResult['message'];
        }

        return $this->container->view->render($response, 'user/manageMembership.html.twig', $result);

    }

    public function cancelMembershipAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {

        $result = $this->membershipServices->getMemberByMemberId((int)$args['memberId']);

        if ($result['exception'] OR $result['member']['membership']->getCancelled()){

            return $this->container->view->render($response, 'userNotification.twig', array('exception' => true,
                                                                                            'message' => 'This membership has already been cancelled'));
        }

        if ($request->isPost()){

            $form_data = $request->getParsedBody();
            $data['reasonForCancel'] = $form_data['reasonForCancel'];
            $cancelResult = $this->membershipServices->cancelMembership((int)$args['memberId'], $data, true, $request);

            return $this->container->view->render($response, 'userNotification.twig', $cancelResult);
        }

        return $this->container->view->render($response, 'user/cancelMembership.html.twig', $result);
    }

    public function sendInvoiceToUserAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        //$user = $this->userServices->getUserById($_SESSION['user_id']);
        $invoiceId = (int)$args['invoiceId'];
        $result = $this->mailServices->sendInvoiceToUser($invoiceId, $_SESSION['user_id'], $request);

        return $this->container->view->render($response, 'userNotification.twig', $result);
    }

    public function newsletterArticleAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        if ($request->isPost()){

            $parsedBody = $request->getParsedBody();

            if ($parsedBody['title'] != NULL AND $parsedBody['text'] != NULL){

                $result = $this->userServices->addNewsletterArticle($request->getParsedBody(), $_SESSION['user_id']);
                return $this->container->view->render($response, 'userNotification.twig', $result);
            }
            return $this->container->view->render($response, 'user/newsletterArticle.html.twig', array(
                'exception' => true,
                'message' => 'One or more fields are not correct or missing',
                'isPost' => true,
                'form' => $parsedBody
                ));
        }

        return $this->container->view->render($response, 'user/newsletterArticle.html.twig');
    }

    public function newslettersAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {

        $result = $this->userServices->getPublishedNewsletters();

        return $this->container->view->render($response, 'user/publishedNewsletters.html.twig', $result);
    }
    
    public function invoicesAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $userId = $_SESSION['user_id'];

        $user = $this->userServices->getUserById($userId);
        if ($user['exception'] == true){

            return $this->container->view->render($response, 'userNotification.twig', $user);
        }
        $invoiceOwner =  $user['user'];

        $result = $this->userServices-> getInvoices($userId);
        $result['user'] = $invoiceOwner;

        return $this->container->view->render($response, 'user/userInvoices.html.twig', $result);
    }

    public function verifyingPaymentAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        if ($request->isPost()) {

            $parsedBody = $request->getParsedBody();
            $data = array(
                'invoice' => (int)$parsedBody['invoice'],
                'receiptUrl' =>  $this->utilsServices->getBaseUrl($request).'/user/singleInvoice/'.(int)$parsedBody['invoice']);

            return $this->container->view->render($response, 'user/verifyingPayment.html.twig', $data);
        }

        $uri = $this->utilsServices->getBaseUrl($request).'/user/home';
        return $response = $response->withRedirect($uri, 200);
    }

    public function downloadPdfInvoiceAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $pdfInv = $this->pdfGenerationServices->generatePdfInvoice((int)$args['invoiceId'],  $_SESSION['user_id'], $request);

        if ($pdfInv['exception']){

            return $this->container->view->render($response, 'userNotification.twig', $pdfInv);
        }

        $resp = $response->withHeader( 'Content-type', 'application/pdf' );
        $resp->write( $pdfInv['pdfInvoice'] );

        return $resp;
    }

    public function downloadPdfReceiptAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $pdfInv = $this->pdfGenerationServices->generatePdfReceipt((int)$args['invoiceId'],  $_SESSION['user_id'], $request);

        if ($pdfInv['exception']){

            return $this->container->view->render($response, 'userNotification.twig', $pdfInv);
        }

        $resp = $response->withHeader( 'Content-type', 'application/pdf' );
        $resp->write( $pdfInv['pdfInvoice'] );

        return $resp;
    }

    //downloadPdfMemberCertificateAction


    public function downloadPdfMemberCertificateAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $member = $this->membershipServices->getMemberByMemberId($args['membershipId']);

        //$pdfInv = $this->pdfGenerationServices->generatePdfReceipt((int)$args['invoiceId'],  $_SESSION['user_id'], $request);

        if ($_SESSION['user_role'] != 'ROLE_ADMIN'){

            if($_SESSION['user_id'] != $member['member']['user']->getId()){
                return $this->container->view->render($response, 'userNotification.twig', array('exception' => true,
                    'message' => 'Certificate not available'));
            }
        }


        if ($member['exception']){
            return $this->container->view->render($response, 'userNotification.twig', $member);
        }
        if (!$member['member']['valid']){
            return $this->container->view->render($response, 'userNotification.twig', array('exception' => true,
                'message' => 'This membership is not valid. Please renew it to download your certificate'));
        }

        $pdfCert = $this->pdfGenerationServices->generatePdfMemberCertificate($member,  $_SESSION['user_id'], $request);

        $resp = $response->withHeader( 'Content-type', 'application/pdf' );
        $resp->write( $pdfCert['pdfInvoice'] );

       // echo json_encode($member);
        return $resp;
    }

    public function testAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {


    }


}