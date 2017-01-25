<?php
/**
 * Created by PhpStorm.
 * User: Danilo G. Zutin
 * Date: 19.05.16
 * Time: 10:28
 */
namespace App\Controller;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;


class AdminController {

    protected $container;

    //Constructor
    public function __construct($container){

        $this->container = $container;
        $this->userServices = $container->get('userServices');
        $this->membershipServices = $container->get('membershipServices');
        $this->mailServices = $container->get('mailServices');
        $this->utilsServices = $container->get('utilsServices');
        $this->billingServices = $container->get('billingServices');
    }


    public function usersAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {

        $users = $this->userServices->findUsersFiltered(array());
        
        return $this->container->view->render($response, 'admin/usersTable.html.twig', array(
            'users' => $users['users']
        ));
    }

    public function registerNewUserAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {

        $val_array = null;
        if ($request->getMethod() == 'POST'){

            $form_data = $request->getParsedBody();
            foreach ($form_data as $key =>$data){

                //TODO: add validation code for each input here
                $val_array[$key] = array('value' => $data,
                    'error' => false);
            }
            $validation = array('exception' => false,
                'message' => 'One or more fields are not valid. Please check your data and submit it again.',
                'fields' => array());

            if ($validation['exception'] == true){

                return $this->container->view->render($response, 'admin/adminRegisterNewUser.html.twig', array(
                    'form_submission' => true,
                    'exception' => true,
                    'message' => $validation['message'],
                    'form' => $val_array));
            }
            else{
                $userService = $this->container->get('userServices');
                $resp = $userService->registerNewUser($form_data);

                if ($resp['exception'] == true){

                    return $this->container->view->render($response, 'admin/adminRegisterNewUser.html.twig', array(
                        'form_submission' => true,
                        'exception' => true,
                        'message' => $resp['message'],
                        'form' => $val_array));
                }
                else{

                    if ($form_data['send_confirmation_mail'] == true){
                        $this->mailServices->sendUserAddedByAdminEmail($resp['user'], $request);
                    }

                    return $this->container->view->render($response, 'admin/adminRegisterNewUser.html.twig', array(
                        'form_submission' => true,
                        'exception' => false,
                        'message' => 'User successfully added. ID: '.$resp['user']->getId(),
                        'form' => $val_array));
                }
            }
        }
        else{

            return $this->container->view->render($response, 'admin/adminRegisterNewUser.html.twig', array(
                'form_submission' => false,
                'exception' => false,
                'message' => 'nope',
                'form' => $val_array));
        }
    }

    public function viewUserProfileAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $userId = $args['userId'];
        $resp =  $this->userServices->getUserById($userId);
        //$resultMemberships = $this->membershipServices->getMembershipsForUser($userId);

        if ($resp['exception'] == false){
            $resetPasswordLink = $request->getUri()->getBaseUrl(). '/resetPassword/'.$resp['user']->getProfileKey();
        }
        else{
            $resetPasswordLink = null;
        }

        //convert the data to be shown in the form
        foreach ($resp['user'] as $key =>$data){
            $user[$key] = array('value' => $data,
                'error' => false);
        }
        $validation = array('exception' => false,
            'message' => '',
            'fields' => array());

        return $this->container->view->render($response, 'admin/adminEditUser.html.twig', array(
            'form_submission' => false,
            'exception' => $resp['exception'],
            'message' => $resp['message'],
            'resetPasswordLink' => $resetPasswordLink,
            'form' => $user));
    }

    public function saveUserProfileAction(ServerRequestInterface $request, ResponseInterface $response, $args)
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

            return $this->container->view->render($response, 'admin/adminEditUser.html.twig', array(
                'form_submission' => true,
                'exception' => $form_validation['exception'],
                'message' => $form_validation['message'],
                'form' => $val_array));
        }
        else{
            $userId = $args['userId'];

            $userService = $this->container->get('userServices');
            $resp = $userService->updateUserProfile($userId, $form_data);

            //convert the data to be shown in the form
            foreach ($resp['user'] as $key =>$data){
                $user[$key] = array('value' => $data,
                    'error' => false);
            }

            return $this->container->view->render($response, 'admin/adminEditUser.html.twig', array(
                'form_submission' => true,
                'exception' => $resp['exception'],
                'message' => $resp['message'],
                'form' => $user));

        }
    }

    public function resetPasswordAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $userId = (int)$args['userId'];

        $resp = $this->userServices->getUserById($userId);
        $mailServices = $this->container->get('mailServices');
        $resetResp = $mailServices->sendResetPasswordMail($resp['user'], $request);

        return $this->container->view->render($response, 'userNotificationMail.twig', array('mailResponse' => $resetResp));
    }

    public function createBulkMailUsersAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {

        return $this->container->view->render($response, 'admin/adminWriteBulkMail.html.twig', array(
            'user_role' => $_SESSION['user_role']));
    }

    public function verifyBulkMailUsersAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $form_data = $request->getParsedBody();
        $respMail = $this->mailServices->highlightPlaceholders($form_data['subject'], $form_data['emailBody']);

        return $this->container->view->render($response, 'admin/adminVerifyBulkMail.html.twig', array(
            'highlightedBody' => $respMail['body'],
            'highlightedSubject' => $respMail['subject'],
            'form' => $form_data));

    }

    public function membersAction(ServerRequestInterface $request, ResponseInterface $response, $args) 
    {

        $post_data = $request->getParsedBody();

        //var_dump($post_data);
        
        $resp = $this->utilsServices->processFilterForMembersTable($post_data);

        $membership_filter = $resp['membership_filter'];
        $user_filter = $resp['user_filter'];
        $validity_filter = $resp['ValidityFilter'];
        $filter_form = $resp['filter_form'];

        //get the list of members based on the filter
        $membersResp = $this->membershipServices->getMembers($membership_filter, $user_filter, $validity_filter['validity'], $validity_filter['onlyValid'], $validity_filter['onlyExpired'], $validity_filter['never_validated']);

        if ($membersResp['exception']){

            return $this->container->view->render($response, 'userNotification.twig', array('message' => $membersResp['message']));
        }
        
        return $this->container->view->render($response, 'admin/membersTable.html.twig', array(
            'exception' => $membersResp['exception'],
            'message' => $membersResp['message'],
            'membershipTypes' => $filter_form['membershipTypes'],
            'memberGrades' => $filter_form['memberGrades'],
            'validity' => $filter_form['validity'],
            'members' => $membersResp['members'],
            'form' => $post_data
        ));
    }

    public function memberAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $memberId = (int)$args['memberId'];

        $form_submission = false;
        if ($request->isPost()){

            $memberResp = $this->membershipServices->getMemberByMemberId($memberId);
            $userId = $memberResp['member']['user']->getId();
            $membershipTypeId = $memberResp['member']['membership']->getMembershipTypeId();

            $form_data = $request->getParsedBody();
            $membershipData['comments'] = $form_data['comments'];
            $membershipData['membershipTypeId'] = $form_data['membershipTypeId'];
            $membershipData['membershipGrade'] = $form_data['membershipGrade'];
            $membershipData['cancelled'] = $form_data['cancelled'];
            $membershipData['reasonForCancel'] = $form_data['reasonForCancel'];

            $form_submission = true;
            $updateMemberResult = $this->membershipServices->addUpdateMember($userId, NULL, $membershipTypeId , $membershipData);

        }

        $membershipTypesResp = $this->membershipServices->getAllMembershipTypes();
        $memberGradesResp = $this->membershipServices->getAllMemberGrades();

        $memberResp = $this->membershipServices->getMemberByMemberId($memberId);

        // get membership types and grades to populate select boxes
        $memberResp['membershipTypes'] = $membershipTypesResp['membershipTypes'];
        $memberResp['memberGrades'] = $memberGradesResp['memberGrades'];

        if ($memberResp['exception']){

            return $this->container->view->render($response, 'userNotification.twig', $memberResp);
        }

        if ($form_submission){
            $memberResp['form_submission'] = true;
            $memberResp['message'] = $updateMemberResult['message'];
        }

        return $this->container->view->render($response, 'admin/adminEditMembership.html.twig', $memberResp);
    }


    public function createBulkMailMembersAction(ServerRequestInterface $request, ResponseInterface $response, $args) 
    {
        $post_data = $request->getParsedBody();
        $resp_process_filter = $this->utilsServices->processFilterForMembersTable($post_data);
        $filter_form = $resp_process_filter['filter_form'];

        return $this->container->view->render($response, 'admin/adminWriteBulkMailMembers.html.twig', array(
            'membershipTypes' => $filter_form['membershipTypes'],
            'memberGrades' => $filter_form['memberGrades'],
            'validity' => $filter_form['validity'],
            'form' => $post_data));
    }

    public function verifyBulkMailMembersAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $form_data = $request->getParsedBody();
        $resp_process_filter = $this->utilsServices->processFilterForMembersTable($form_data);
        $filter_form = $resp_process_filter['filter_form'];

        $respMail = $this->mailServices->highlightPlaceholders($form_data['subject'], $form_data['emailBody']);

        return $this->container->view->render($response, 'admin/adminVerifyBulkMailMembers.html.twig', array(
            'highlightedBody' => $respMail['body'],
            'highlightedSubject' => $respMail['subject'],
            'membershipTypes' => $filter_form['membershipTypes'],
            'memberGrades' => $filter_form['memberGrades'],
            'validity' => $filter_form['validity'],
            'form' => $form_data));
    }

    public function manageRenewalsAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {

        $membershipId = (int)$args['membershipId'];
        $renewalsRes = $this->membershipServices->getValiditiesForMembershipId($membershipId);

        return $this->container->view->render($response, 'admin/adminManageRenewals.html.twig', $renewalsRes);

    }

    public function userInvoicesAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $userId = (int)$args['userId'];

        if ($userId == -1){
            $userId = NULL;
            $invoiceOwner = NULL;
        }
        else{
            $user = $this->userServices->getUserById($userId);
            if ($user['exception'] == true){

                return $this->container->view->render($response, 'userNotification.twig', $user);
            }
            $invoiceOwner =  $user['user'];
        }

        $result = $this->userServices-> getInvoices($userId);
        $result['user'] =$invoiceOwner;

        return $this->container->view->render($response, 'admin/adminUserInvoicesReceipts.html.twig', $result);
    }

    public function invoicePaymentsAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $invoiceId = (int)$args['invoiceId'];
        
        $result = $this->billingServices->getPaymentsForInvoice($invoiceId);
        
        if ($result['exception'] == false){
            $onPaymentActions = json_decode($result['invoiceData']['invoice']->getOnPaymentActions());

            $result['onPaymentActions'] = $onPaymentActions;
        }
        return $this->container->view->render($response, 'admin/adminManagePayments.html.twig', $result);
    }

    public function singleInvoiceAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $invoiceId = $args['invoiceId'];
        //Retrieve the user creating the invoice

        $respInvoiceData = $this->userServices->getInvoiceDataForUser($invoiceId, NULL);

        if ($respInvoiceData['exception'] == true){

            return $this->container->view->render($response, 'userNotification.twig', $respInvoiceData);
        }

        $userResp = $this->userServices->getUserById($respInvoiceData['invoice']->getUserId());

        if ($userResp['exception'] == false){
            $user =  $userResp['user'];
        }
        else{
            return $this->container->view->render($response, 'userNotification.twig', $userResp);
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

        $isPost = false;

        if ($request->isPost()){
            $messagePaypal = $request->getBody();
            $isPost = true;
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
            'totalPrice' =>  $totalPrice_formatted,
            'amountPaid' => $amountPaid_formatted,
            'outstandingAmount' => $outstandingAmount_formatted,
            'outstandingAmount_paypal' => $respInvoiceData['outstandingAmount'], //original US locale to be passed to paypal.
            'paypal_ipn_url' => $request->getUri()->withPath($this->container->router->pathFor('paypal_ipn')),
            'invoiceLink' =>  $request->getUri()->withPath($this->container->router->pathFor('singleInvoiceAdmin', ['invoiceId' => $respInvoiceData['invoice']->getId()])),
            'message' => $respInvoiceData['message'],
            'isPost' =>$isPost));
    }

    public function createNewsletterAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        if ($request->isPost()){

            $data = $request->getParsedBody();
            $result = $this->userServices->createUpdateNewsletter(-1, $data, $_SESSION['user_id']);

            if ($result['exception'] == false){

                $uri = $request->getUri()->withPath($this->container->router->pathFor('saveNewsletterAdmin', ['newsletterId' => $result['newsletter']->getId()]));
                return $response = $response->withRedirect($uri, 200);
            }
            return $this->container->view->render($response, 'userNotification.twig', $result);
        }

        return $this->container->view->render($response, 'admin/newsletter.html.twig');

    }

    public function newslettersAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $result = $this->userServices->getNewsletters();

        return $this->container->view->render($response, 'admin/newsletters.html.twig', $result);
    }

    public function newsletterAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $newsletterId = (int)$args['newsletterId'];

        if ($newsletterId != -1){

            $newsletterResult = $this->userServices->getNewsletter($newsletterId);

            if ($newsletterResult['exception'] == true){
                return $this->container->view->render($response, 'userNotification.twig', $newsletterResult);
            }
            $result = $this->userServices->getNewsletterArticles($newsletterId);
            $result['newsletter'] = $newsletterResult['newsletter'];
            $result['publicLink'] = $request->getUri()->withPath($this->container->router->pathFor('publicNewsletter', ['key' => $result['newsletter']->getPublicKey()]));
        }
        $result['newsletterId'] = $newsletterId;

        return $this->container->view->render($response, 'admin/newsletter.html.twig', $result);
    }

    public function saveNewsletterAdminAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $newsletterId = (int)$args['newsletterId'];
        $data = $request->getParsedBody();

        $result = $this->userServices->createUpdateNewsletter($newsletterId, $data, $_SESSION['user_id']);
        $articleResult = $this->userServices->getNewsletterArticles($newsletterId);
        $result['articles'] = $articleResult['articles'];
        $result['isPost'] = true;

        return $this->container->view->render($response, 'admin/newsletter.html.twig', $result);
    }

    public function newsletterPreviewAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $key = $args['key'];
        $result = $this->userServices->assemblePublicNewsletter($key, true);
        $result['publicLink'] = $request->getUri()->withPath($this->container->router->pathFor('publicNewsletter', ['key' => $result['newsletter']->getPublicKey()]));
        $result['baseUrl'] = $request->getUri()->getBaseUrl();
        return $this->container->view->render($response, 'newsletter/newsletter.html.twig', $result);
    }


}