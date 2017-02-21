<?php
/**
 * Created by PhpStorm.
 * User: Danilo G. Zutin
 * Date: 19.05.16
 * Time: 10:28
 */
namespace App\Controller;
use App\Entity\MembershipType;
use App\Entity\ShoppingCartItem;
use App\Entity\Billing;
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

    public function systemSettingsAction(ServerRequestInterface $request, ResponseInterface $response)
    {

        if ($request->isPost()){

          //  var_dump($request->getParsedBody());
            $systemInfoSave = $this->userServices->saveSystemInfo($request->getParsedBody());
            //$settings = $systemInfo['settings'];
        }

        $systemInfo = $this->userServices->getSystemInfo();
        $settings = $systemInfo['settings'];

        $form = array(
            array('type' => 'text', 'name' => 'acronym', 'label' => "Acronym", 'value' => $settings->getAcronym(), 'required' => true),
            array('type' => 'text', 'name' => 'nameOfOrganization', 'label' => "Name of Organisation", 'value' => $settings->getNameOfOrganization(), 'required' => true, 'readonly' => false),
            array('type' => 'url', 'name' => 'orgWebsite', 'label' => "Organisation's Website", 'value' => $settings->getOrgWebsite(), 'required' => false),
            array('type' => 'textarea', 'name' => 'disclaimer', 'label' => "Disclaimer", 'value' => $settings->getDisclaimer(), 'required' => false),
            array('type' => 'email', 'name' => 'email', 'label' => "E-mail Address", 'value' => $settings->getEmail(), 'required' => true),
            array('type' => 'text', 'name' => 'street', 'label' => "Street", 'value' => $settings->getStreet(), 'required' => true),
            array('type' => 'text', 'name' => 'city', 'label' => "City", 'value' => $settings->getCity(), 'required' => true),
            array('type' => 'text', 'name' => 'zip', 'label' => "ZIP", 'value' => $settings->getZip(), 'required' => true),
            array('type' => 'text', 'name' => 'vat', 'label' => "VAT Number", 'value' => $settings->getVat(), 'required' => false),
            array('type' => 'phone', 'name' => 'phone', 'label' => "Phone", 'value' => $settings->getPhone(), 'required' => false),
            array('type' => 'text', 'name' => 'googleAnalyticsTrackingId', 'label' => "Google Analytics ID", 'value' => $settings->getGoogleAnalyticsTrackingId(), 'required' => false),
            array('type' => 'text', 'name' => 'registrationNumber', 'label' => "Registration Number", 'value' => $settings->getRegistrationNumber(), 'required' => false),
            array('type' => 'country', 'name' => 'country', 'label' => "Country", 'value' => $settings->getCountry(), 'required' => true),
            array('type' => 'section', 'name' => 'section', 'label' => "Payment Options", 'required' => true),
            array('type' => 'number', 'name' => 'vat_rate', 'label' => "VAT Rate (%)", 'value' => $settings->getVatRate(), 'required' => false),
            array('type' => 'text', 'name' => 'systemCurrency', 'label' => "System currency", 'value' => $settings->getSystemCurrency(), 'required' => true),
            array('type' => 'select', 'name' => 'paypalActive', 'label' => "Paypal Active?", 'value' => $settings->getPaypalActive(), 'required' => true, 'options' => array(
                array('value' => '1', 'name' => 'Active'),
                array('value' => '0', 'name' => 'Not Active')
                )),
            array('type' => 'email', 'name' => 'paypalEmail', 'label' => "Paypal e-mail Address", 'value' => $settings->getPaypalEmail(), 'required' => false),
            array('type' => 'select', 'name' => 'paypalSandboxModeActive', 'label' => "Paypal Sandbox Mode?", 'value' => $settings->getPaypalSandboxModeActive(), 'required' => true, 'options' => array(
                array('value' => '1', 'name' => 'Sandbox Active'),
                array('value' => '0', 'name' => 'Sandbox Not Active')
            )),
            array('type' => 'select', 'name' => 'wireTransferActive', 'label' => "Wire Transfer active?", 'value' => $settings->getWireTransferActive(), 'required' => true, 'options' => array(
                array('value' => '1', 'name' => 'Active'),
                array('value' => '0', 'name' => 'Not Active')
            )),
            array('type' => 'text', 'name' => 'iban', 'label' => "IBAN", 'value' => $settings->getIban(), 'required' => true),
            array('type' => 'text', 'name' => 'bic', 'label' => "BIC", 'value' => $settings->getBic(), 'required' => true),
            array('type' => 'text', 'name' => 'bankName', 'label' => "Bank Name", 'value' => $settings->getBankName(), 'required' => false),
            array('type' => 'text', 'name' => 'bankAddress', 'label' => "Bank Address", 'value' => $settings->getBankAddress(), 'required' => false),
            );

        return $this->container->view->render($response, 'admin/adminEditSystemSettings.html.twig', array(
            'form' => $form,
            'form_submission' => $request->isPost(),
            'message' => $systemInfoSave['message']
        ));
    }

    public function membershipTypesAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        if ($request->isPost()){
            $this->membershipServices->saveMembershipTypeOrder($request->getParsedBody());
        }

        $membershipTypesResp = $this->membershipServices->getAllMembershipTypes('listingOrder');
        return $this->container->view->render($response, 'admin/membershipTypesTable.html.twig', $membershipTypesResp);
    }

    public function membershipTypeAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        if ($request->isPost()){

            $membershipTypesResp = $this->membershipServices->saveAddMembershipType((int)$args['typeId'], $request->getParsedBody());
            $type = $membershipTypesResp['membershipType'];
        }
        else{
            $membershipTypesResp = $this->membershipServices->getMembershipTypeById((int)$args['typeId']);
            $type = $membershipTypesResp['membershipType'];
        }

        if ($type == null){
            $type = new MembershipType();
        }

        $form = array(
            array('type' => 'text', 'name' => 'typeName', 'label' => "Name", 'value' => $type->getTypeName(), 'required' => true),
            array('type' => 'select', 'name' => 'selectable', 'label' => "Selectable?", 'value' => $type->getSelectable(), 'required' => true, 'options' => array(
                array('value' => '1', 'name' => 'Selectable'),
                array('value' => '0', 'name' => 'Non selectable')
            )),
            array('type' => 'text', 'name' => 'renewal_threshold', 'label' => "Threshold for renewal (MM-DD)", 'value' => $type->getRenewalThreshold(), 'required' => true),
            array('type' => 'text', 'name' => 'currency', 'label' => "Currency", 'value' => $type->getCurrency(), 'required' => true),
            array('type' => 'text', 'name' => 'fee', 'label' => "Fee", 'value' => $type->getFee(), 'required' => false),
            array('type' => 'select', 'name' => 'recurrence', 'label' => "Recurrence", 'value' => $type->getRecurrence(), 'required' => false, 'options' => array(
                array('value' => 'year', 'name' => 'yearly'),
                array('value' => null, 'name' => 'n/a - free')
            )),
            array('type' => 'textarea', 'name' => 'description', 'label' => "Description", 'value' => $type->getDescription(), 'required' => false),
            array('type' => 'number', 'name' => 'numberOfRepresentatives', 'label' => "Number of Representatives", 'value' => $type->getNumberOfRepresentatives(), 'required' => false),
            array('type' => 'text', 'name' => 'prefix', 'label' => "Prefix", 'value' => $type->getPrefix(), 'required' => true),
            array('type' => 'select', 'name' => 'useGlobalMemberNumberAssignment', 'label' => "Use global member ID assignment?", 'value' => $type->getUseGlobalMemberNumberAssignment(), 'required' => false, 'options' => array(
                array('value' => '1', 'name' => 'Use global')
            )),
            array('type' => 'number', 'name' => 'initialMemberId', 'label' => "Initial Member ID", 'value' => $type->getInitialMemberId(), 'required' => false),
            array('type' => 'textarea', 'name' => 'terms', 'label' => "Membership Terms", 'value' => $type->getTerms(), 'required' => false),
        );


        return $this->container->view->render($response, 'admin/adminAddEditMembershipType.html.twig', array(
            'form' => $form,
            'form_submission' => $request->isPost(),
            'message' => $membershipTypesResp['message']
        ));
    }


    public function impersonateUserAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $userId = (int)$args['userId'];

        $impersonateResp = $this->userServices->impersonateUser($userId, $request);

        if ($impersonateResp['exception']){
            return $this->container->view->render($response, 'userNotification.twig', $impersonateResp);
        }
        return $response = $response->withRedirect($impersonateResp['redirectUrl'], 200);
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
        $userId = (int)$args['userId'];
        $resp =  $this->userServices->getUserById($userId);
        //$resultMemberships = $this->membershipServices->getMembershipsForUser($userId);

        if ($resp['exception'] == true){
            return $this->container->view->render($response, 'userNotificationMail.twig', array('mailResponse' => $resp));
        }

        $billingInfo = $this->userServices->getBillingInfoForUser($userId);

        if ($billingInfo['exception']){
            $billing =  null;
        }
        else{
            $billing = $billingInfo['billing'];
        }

        return $this->container->view->render($response, 'admin/adminEditUser.html.twig', array(
            'form_submission' => false,
            'exception' => $resp['exception'],
            'message' => $resp['message'],
            'billingInfo' => $billing,
            'user' => $resp['user']));
    }

    public function saveUserProfileAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $form_data = $request->getParsedBody();

        $userId = $args['userId'];
        
        $resp = $this->userServices->updateUserProfile($userId, $form_data, $form_data);

        if ($resp['exception'] == true){
            return $this->container->view->render($response, 'userNotificationMail.twig', array('mailResponse' => $resp));
        }

        // save  billing Info for user
        $billing['name'] = $form_data['billing_name'];
        $billing['institution'] = $form_data['billing_institution'];
        $billing['street'] = $form_data['billing_street'];
        $billing['country'] = $form_data['billing_country'];
        $billing['city'] = $form_data['billing_city'];
        $billing['zip'] = $form_data['billing_zip'];
        $billing['vat'] = $form_data['billing_vat'];
        $billing['reference'] = $form_data['billing_reference'];

        $billingInfo = $this->userServices->createOrUpdateBillingInfo($billing, $resp['user']);

        if ($billingInfo['exception']){
            $billing =  null;
        }
        else{
            $billing = $billingInfo['billing'];
        }

        return $this->container->view->render($response, 'admin/adminEditUser.html.twig', array(
            'form_submission' => true,
            'exception' => $resp['exception'],
            'message' => $resp['message'],
            'billingInfo' => $billing,
            'user' => $resp['user']));

    }

    public function sendInvoiceToUserAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $invoiceId = (int)$args['invoiceId'];
        $result = $this->mailServices->sendInvoiceToUser($invoiceId, NULL, $request);

        return $this->container->view->render($response, 'userNotification.twig', $result);
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
        $resp = $this->utilsServices->processFilterForMembersTable($post_data, (int)$args['userId']);

        $membership_filter = $resp['membership_filter'];
        $user_filter = $resp['user_filter'];
        $validity_filter = $resp['ValidityFilter'];
        $filter_form = $resp['filter_form'];

        //get the list of members based on the filter
        $membersResp = $this->membershipServices->getMembers($membership_filter, $user_filter, $validity_filter['validity'], $validity_filter['onlyValid'], $validity_filter['onlyExpired'], $validity_filter['never_validated']);

        if ($membersResp['exception']){

            return $this->container->view->render($response, 'userNotification.twig', array('message' => $membersResp['message']));
        }

        $membershipTypeAvailable = false;
        $singleUser = false;
        if (isset($args['userId'])){
            $membershipTypeAvailable = $this->utilsServices->newMembershipPossible((int)$args['userId']);
            $singleUser = true;
        }
        
        return $this->container->view->render($response, 'admin/membersTable.html.twig', array(
            'exception' => $membersResp['exception'],
            'message' => $membersResp['message'],
            'membershipTypes' => $filter_form['membershipTypes'],
            'memberGrades' => $filter_form['memberGrades'],
            'validity' => $filter_form['validity'],
            'members' => $membersResp['members'],
            'userId' => (int)$args['userId'],
            'form' => $post_data,
            'singleUser' => $singleUser,
            'membershipTypeAvailable' => $membershipTypeAvailable
        ));
    }

    public function memberAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $memberId = (int)$args['memberId'];

        if ($request->isPost()){

            $form_data = $request->getParsedBody();

            $memberResp = $this->membershipServices->getMemberByMemberId($memberId);
            $userId = $memberResp['member']['user']->getId();
            $membershipTypeId = $memberResp['member']['membership']->getMembershipTypeId();
            $membershipData['comments'] = $form_data['comments'];
            $membershipData['membershipTypeId'] = (int)$form_data['membershipTypeId'];
            $membershipData['membershipGrade'] = (int)$form_data['membershipGrade'];
            $membershipData['cancelled'] = $form_data['cancelled'];
            $membershipData['reasonForCancel'] = $form_data['reasonForCancel'];

            $updateMemberResult = $this->membershipServices->addUpdateMember($userId, NULL, $membershipTypeId , $membershipData);

            $membershipsTypes = $this->membershipServices->getMembershipTypeAndStatusOfUser($updateMemberResult['member']['user'], NULL, false, true);
            $memberGradesResp = $this->membershipServices->getAllMemberGrades();

            $updateMemberResult['membershipTypes'] = $membershipsTypes['membershipTypes'];
            $updateMemberResult['memberGrades'] = $memberGradesResp['memberGrades'];
            $updateMemberResult['form_submission'] = true;
            return $this->container->view->render($response, 'admin/adminEditMembership.html.twig', $updateMemberResult);
        }

        $memberResp = $this->membershipServices->getMemberByMemberId($memberId);

        if ($memberResp['exception']){
            return $this->container->view->render($response, 'userNotification.twig', $memberResp);
        }

        $membershipsTypes = $this->membershipServices->getMembershipTypeAndStatusOfUser($memberResp['member']['user'], NULL, false, true);
        $memberGradesResp = $this->membershipServices->getAllMemberGrades();
        //echo json_encode($membershipsTypes);

        // get membership types and grades to populate select boxes
        $memberResp['membershipTypes'] = $membershipsTypes['membershipTypes'];
        $memberResp['memberGrades'] = $memberGradesResp['memberGrades'];

        return $this->container->view->render($response, 'admin/adminEditMembership.html.twig', $memberResp);
    }

    public function addMemberAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $userResp = $this->userServices->getUserById((int)$args['userId']);

        if ($userResp['exception'] == true){
            return $this->container->view->render($response, 'userNotification.twig', $userResp);
        }

        if ($request->isPost()){

            $form_data = $request->getParsedBody();

            $membershipData['comments'] = $form_data['comments'];
            $membershipData['membershipTypeId'] = $form_data['membershipTypeId'];

            $updateMemberResult = $this->membershipServices->addUpdateMember($userResp['user']->getId(), NULL, (int)$form_data['membershipTypeId'] , $membershipData);

            $membershipsTypes = $this->membershipServices->getMembershipTypeAndStatusOfUser($updateMemberResult['member']['user'], NULL, false, true);
            $memberGradesResp = $this->membershipServices->getAllMemberGrades();

            $updateMemberResult['membershipTypes'] = $membershipsTypes['membershipTypes'];
            $updateMemberResult['memberGrades'] = $memberGradesResp['memberGrades'];
            $updateMemberResult['form_submission'] = true;
            return $this->container->view->render($response, 'admin/adminEditMembership.html.twig', $updateMemberResult);
        }

        $membershipsTypes = $this->membershipServices->getMembershipTypeAndStatusOfUser($userResp['user'], NULL, false, true);
        $memberGradesResp = $this->membershipServices->getAllMemberGrades();
        //echo json_encode($membershipsTypes);

        // get membership types and grades to populate select boxes
        $resp['membershipTypes'] = $membershipsTypes['membershipTypes'];
        $resp['memberGrades'] = $memberGradesResp['memberGrades'];
        $resp['user'] = $userResp['user'];

        return $this->container->view->render($response, 'admin/adminAddMembership.html.twig', $resp);

    }


    public function createBulkMailMembersAction(ServerRequestInterface $request, ResponseInterface $response, $args) 
    {
        $resp_process_filter = $this->utilsServices->processFilterForMembersTable(NULL);
        $filter_form = $resp_process_filter['filter_form'];

        return $this->container->view->render($response, 'admin/adminWriteBulkMailMembers.html.twig', array(
            'membershipTypes' => $filter_form['membershipTypes'],
            'memberGrades' => $filter_form['memberGrades'],
            'validity' => $filter_form['validity']));
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
        $result['user'] = $invoiceOwner;

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

        return $this->container->view->render($response, 'admin/singleInvoice.html.twig', array(
            'user' => $user,
            'exception' => $respInvoiceData['exception'],
            'invoiceData' => $respInvoiceData['invoice'],
            'invoiceDate' => $respInvoiceData['invoiceDate'],
            'paidDate' => $respInvoiceData['paidDate'],
            'invoiceDueDate' => $respInvoiceData['invoiceDueDate'],
            'items' => $respInvoiceData['invoiceItems'],
            'issuerData' => $respInvoiceData['issuerData'],
            'totalPrice' =>  $respInvoiceData['totalPrice'],
            'amountPaid' => $respInvoiceData['amountPaid'],
            'outstandingAmount' => $respInvoiceData['outstandingAmount'],
            'outstandingAmount_paypal' => $respInvoiceData['outstandingAmount'],
            'paypal_ipn_url' => $this->utilsServices->getBaseUrl($request).'/paypal_ipn',
            'invoiceLink' => $this->utilsServices->getBaseUrl($request).'/admin/singleInvoice/'.$respInvoiceData['invoice']->getId(),
            'message' => $respInvoiceData['message'],
            'isPost' =>$request->isPost()));
    }

    public function createNewsletterAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $result['createNewsletter'] = true;
        if ($request->isPost()){

            $data = $request->getParsedBody();
            $result = $this->userServices->createUpdateNewsletter(-1, $data, $_SESSION['user_id']);

            if ($result['exception'] == false){

                $uri = $this->utilsServices->getUrlForRouteName($request, 'saveNewsletterAdmin', array('newsletterId' => $result['newsletter']->getId()));
                return $response = $response->withRedirect($uri, 200);
            }
            return $this->container->view->render($response, 'userNotification.twig', $result);
        }

        return $this->container->view->render($response, 'admin/newsletter.html.twig', $result);

    }

    public function newslettersAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $result = $this->userServices->getNewsletters();

        return $this->container->view->render($response, 'admin/newsletters.html.twig', $result);
    }

    public function newsletterAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $newsletterId = (int)$args['newsletterId'];

        $newsletterResult = $this->userServices->getNewsletter($newsletterId);

        if ($newsletterResult['exception'] == true){
            return $this->container->view->render($response, 'userNotification.twig', $newsletterResult);
        }
        $result = $this->userServices->getNewsletterArticles($newsletterId, $newsletterResult['newsletter']->getPublished());
        $result['newsletter'] = $newsletterResult['newsletter'];
        $result['publicLink'] = $this->utilsServices->getBaseUrl($request).'/newsletter/'.$result['newsletter']->getPublicKey();
        $result['createNewsletter'] = false;

        return $this->container->view->render($response, 'admin/newsletter.html.twig', $result);
    }

    //save existing newsletter
    public function saveNewsletterAdminAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $newsletterId = (int)$args['newsletterId'];
        $data = $request->getParsedBody();

        $result = $this->userServices->createUpdateNewsletter($newsletterId, $data, $_SESSION['user_id']);
        $articleResult = $this->userServices->getNewsletterArticles($newsletterId,  $result['newsletter']->getPublished());
        $result['articles'] = $articleResult['articles'];
        $result['isPost'] = true;
        $result['createNewsletter'] = false;

        return $this->container->view->render($response, 'admin/newsletter.html.twig', $result);
    }

    public function newsletterPreviewAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $key = $args['key'];

        $baseUrl = $this->utilsServices->getBaseUrl($request);
        $result = $this->userServices->assemblePublicNewsletter($key, true, $baseUrl, false);

        return $this->container->view->render($response, 'newsletter/newsletter.html.twig', $result);
    }

    //save existing newsletter article
    public function newsletterArticleAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $articleId = (int)$args['articleId'];

        if ($request->isPost()){

            $parsedBody = $request->getParsedBody();

            if ($parsedBody['title'] != NULL AND $parsedBody['text'] != NULL){

                $resultUpdate = $this->userServices->updateNewsletterArticle($articleId, $parsedBody);

                var_dump($parsedBody['imageFileName']);
                $resultUpdate['isPost'] = true;
                return $this->container->view->render($response, 'admin/adminNewsletterArticle.html.twig', $resultUpdate);
            }
            else{
                return $this->container->view->render($response, 'user/newsletterArticle.twig', array(
                    'exception' => true,
                    'message' => 'One or more fields are not correct or missing'));
            }
        }

        $articleResult = $this->userServices->getSingleArticle($articleId);
        if ($articleResult['exception'] == true){
            return $this->container->view->render($response, 'userNotification.twig', $articleResult);
        }

        return $this->container->view->render($response, 'admin/adminNewsletterArticle.html.twig', $articleResult);
    }

    public function createBulkMailNewsletterAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $key = $args['key'];
        $baseUrl = $this->utilsServices->getBaseUrl($request);
        $resultNewsletter = $this->userServices->assemblePublicNewsletter($key, true, $baseUrl, false);
        $htmlNewsletter = $this->mailServices->createHtmlNewsletter($resultNewsletter);

        $resp_process_filter = $this->utilsServices->processFilterForMembersTable(NULL);
        $filter_form = $resp_process_filter['filter_form'];

        return $this->container->view->render($response, 'admin/adminSendNewsletter.html.twig', array(
            'membershipTypes' => $filter_form['membershipTypes'],
            'memberGrades' => $filter_form['memberGrades'],
            'validity' => $filter_form['validity'],
            'newsletter' => $resultNewsletter));
           // 'htmlNewsletter' => $htmlNewsletter));
    }

    public function deleteUserAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $userId = (int)$args['userId'];
        $resp =  $this->userServices->getUserById($userId);

        if ($resp['exception'] == true){
            return $this->container->view->render($response, 'userNotificationMail.twig', array('mailResponse' => $resp));
        }

        if ($request->isPost()){
            //delete user ...
            if ($userId == $_SESSION['user_id']){
                return $this->container->view->render($response, 'admin/adminDeleteUser.html.twig', array(
                    'form_submission' => true,
                    'purgeResult' => null,
                    'purgeResult_json' => null,
                    'exception' => true,
                    'user' => $resp['user'],
                    'message' => 'You are currently logged in with the same account you are trying to delete. Sign in with a different account and try again.'));
            }

            $purgeResult = $this->userServices->purgeUser($userId);

            return $this->container->view->render($response, 'admin/adminDeleteUser.html.twig', array(
                'form_submission' => true,
                'purgeResult' => $purgeResult,
                'purgeResult_json' => json_encode($purgeResult),
                'exception' => false,
                'user' => $resp['user'],
                'message' => 'Delete operation was carried out. See protocol below for more details.'));
        }

        $membershipsOfUser = $this->membershipServices->getMembershipsForUser($userId, true);

        return $this->container->view->render($response, 'admin/adminDeleteUser.html.twig', array(
            'form_submission' => false,
            'purgeResult' => null,
            'purgeResult_json' => null,
            'exception' => $resp['exception'],
            'memberships' => $membershipsOfUser,
            'user' => $resp['user']));
    }

    public function invoiceAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $userId = (int)$args['userId'];
        $resp =  $this->userServices->getUserById($userId);

        $invoiceId = null;
        if (isset($args['invoiceId'])){
            $invoiceId = (int)$args['invoiceId'];

        }

        $respInvoiceData = null;
        $resultsGenInvoice = null;
        if ($request->isPost()){

            $form_data = $request->getParsedBody();

            if (isset($form_data['invoiceId'])){
                $invoiceId = $form_data['invoiceId'];
            }

            // create  billing Info for user
            $billingInfo = new Billing();
            $billingInfo->setName($form_data['billing_name']);
            $billingInfo->setInstitution($form_data['billing_institution']);
            $billingInfo->setStreet($form_data['billing_street']);
            $billingInfo->setCountry($form_data['billing_country']);
            $billingInfo->setCity($form_data['billing_city']);
            $billingInfo->setZip($form_data['billing_zip']);
            $billingInfo->setVat($form_data['billing_vat']);
            $billingInfo->setReference($form_data['billing_reference']);

            $itemNames = $form_data['itemName'];
            $unitPrices = $form_data['unitPrice'];
            $quantities = $form_data['quantity'];

           // var_dump($itemNames);

            // create Items
            $i = 0;
            $items[] = new ShoppingCartItem();
            foreach ($itemNames as $itemName){
                
                $cartItem = new ShoppingCartItem();
                $cartItem->setName($itemName);
                $cartItem->setQuantity($quantities[$i]);
                $cartItem->setUnitPrice($unitPrices[$i]);
                $cartItem->setTotalPrice($unitPrices[$i]*$quantities[$i]);
                $cartItem->setUserId($userId);
                $items[$i] = $cartItem;
                $i++;
            }
            //var_dump($items);
            //on payment actions

            $actionName = $form_data['actionName'];

            $params = $form_data['params'];
            $params = array_map("intval", explode(",", $params));

            $onPaymentActions = array(array ('actionName' => $actionName,
                'membershipIds' => $params));

            $userResp = $this->userServices->getUserById($userId);
            $user =  $userResp['user'];

            //remove spaces from currency code
            $currency = str_replace(' ', '', $form_data['currency']);
            $currency = preg_replace('/\s+/', '', $currency);

            $resultsGenInvoice = $this->userServices->generateUpdateInvoiceForUser($invoiceId, $user, $billingInfo, $items, json_encode($onPaymentActions), false, $request, $currency, $form_data['vatRate'], $form_data['createDate'], $form_data['dueDate']);
            $respInvoiceData = $this->userServices->getInvoiceDataForUser($resultsGenInvoice['invoiceId'], $userId);

            return $this->container->view->render($response, 'admin/adminAddEditInvoice.html.twig', array(
                'form_submission' => true,
                'exception' => $resultsGenInvoice['exception'],
                'message' => $resultsGenInvoice['message'],
                'billingInfo' => $billingInfo,
                'user' => $resp['user'],
                'resultsGenInvoice' => $resultsGenInvoice,
                'invoiceData' => $respInvoiceData));
        }
        else{
            $billing  = null;
            if ($invoiceId != null){
                $respInvoiceData = $this->userServices->getInvoiceDataForUser($invoiceId);
                
            }
            else{
                $billingInfo = $this->userServices->getBillingInfoForUser($userId);

                if ($billingInfo['exception']){

                    // create  billing Info for user
                    $billing = new Billing();
                    $billing->setName($resp['user']->getTitle().' '.$resp['user']->getFirstName().' '.$resp['user']->getLastName());
                    $billing->setInstitution($resp['user']->getInstitution());
                    $billing->setStreet($resp['user']->getStreet());
                    $billing->setCountry($resp['user']->getCountry());
                    $billing->setCity($resp['user']->getCity());
                    $billing->setZip($resp['user']->getZip());
                }
                else{
                    $billing = $billingInfo['billing'];
                }
            }

        }


        return $this->container->view->render($response, 'admin/adminAddEditInvoice.html.twig', array(
            'form_submission' => false,
            'exception' => $resp['exception'],
            'message' => $resp['message'],
            'billingInfo' => $billing,
            'user' => $resp['user'],
            'invoiceData' => $respInvoiceData));
    }



}