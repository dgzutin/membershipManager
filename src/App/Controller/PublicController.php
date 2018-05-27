<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 19.05.16
 * Time: 13:13
 */
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Entity\User;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use \Httpful\Request;
use \Exception;


class PublicController {
    protected $container;
    //Constructor
    public function __construct($container) {

        $this->container = $container;
        $this->systemInfo = $container->get('userServices')->getSystemInfo();
        $this->membershipServices = $container->get('membershipServices');
        $this->billingServices = $container->get('billingServices');
        $this->userServices = $container->get('userServices');
        $this->utilsServices = $container->get('utilsServices');
        $this->linkedInServices = $container->get('linkedInServices');
    }

    public function loginAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        session_start();
        //Authorizes request here
        if (isset($_SESSION['user_id'])){

            $homeUrl = $this->utilsServices->getUrlForRouteName($request, 'homeUser');
            return $response->withRedirect($homeUrl, 200);
        }
        return $this->container->view->render($response, 'login.html.twig',
            array('linkedInOauthEndpoint' => 'https://www.linkedin.com/oauth/v2/authorization',
                'oauthRedirect' => $this->utilsServices->getUrlForRouteName($request, 'linkedInOauth2Redirect'),
                'linkedInState' => 'login',
                'publicKey' =>  $this->systemInfo['settings']->getReCaptchaPublicKey()
            ));
    }

    public function processLoginAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $userInfo = $request->getParsedBody();
        //verify recaptcha

        if ($userInfo['reCaptchaActive'] == 'true'){

            $verifyCaptchaResult = $this->utilsServices->verifyRecaptcha($userInfo['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

            //var_dump($userInfo['reCaptchaActive']);
            if (!$verifyCaptchaResult->success){

                return $this->container->view->render($response, 'userNotification.twig', array(
                    'exception' => true,
                    'message' => 'Oops... Google thinks you are a robot. Have a nice day'));
            }
        }

        $userService = $this->container->get('userServices');
        $auth_result = $userService->authenticateUser($userInfo['email'],$password = $userInfo['password'] );

        if ($auth_result['exception'] == false){

            session_start();
            $_SESSION['user_id'] = $auth_result['user_id'];
            $_SESSION['user_role'] = $auth_result['user_role'];

            $redirect_url = $this->utilsServices->getBaseUrl($request).'/user/home';

            if (isset($_SESSION['orig_uri'])){

                $redirect_url = $_SESSION['orig_uri'];
            }
            //Log user action (login)
            $auth_result['type'] = SIGN_IN_OUT;
            $auth_result['identityProvider'] = 'Local';
            $this->container->get('userLogger')->info('User logged in', $auth_result);

            return $response->withRedirect($redirect_url, 200);
        }
        
        return $this->container->view->render($response, 'login.html.twig', array(
            'form_submission' => true,
            'exception' => $auth_result['exception'],
            'message' => $auth_result['message']));
    }

    public function homeAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $uri = $this->utilsServices->getBaseUrl($request).'/login';
        return $response = $response->withRedirect($uri);
    }

    public function logoutAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        session_start();
        //Log user action (logout)
        $context['type'] = SIGN_IN_OUT;
        $context['user_id'] = $_SESSION['user_id'];
        $context['user_role'] = $_SESSION['user_role'];
        $this->container->get('userLogger')->info('User logged out', $context);
        session_destroy();
        
        $uri = $this->utilsServices->getBaseUrl($request).'/login';
        return $response = $response->withRedirect($uri, 200);
    }

    public function registerAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        return $this->container->view->render($response, 'register.html.twig', array(
            'publicKey' =>  $this->systemInfo['settings']->getReCaptchaPublicKey()));
    }

    public function processRegisterAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $form_data = $request->getParsedBody();
        $val_array = null;

        //verify recaptcha
        if ($form_data['reCaptchaActive'] == 'true'){

            $verifyCaptchaResult = $this->utilsServices->verifyRecaptcha($form_data['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

            //var_dump($userInfo['reCaptchaActive']);
            if (!$verifyCaptchaResult->success){

                return $this->container->view->render($response, 'userNotification.twig', array(
                    'exception' => true,
                    'message' => 'Oops... Google thinks you are a robot. Have a nice day'));
            }
        }

        foreach ($form_data as $key =>$data){

         //TODO: add validation code for each input here
            $val_array[$key] = array('value' => $data,
                                     'error' => false);
        }

        $validation = array('exception' => false,
                            'message' => 'One or more fields are not valid. Please check your data and submit it again.',
                            'fields' => array());

        if ($validation['exception'] == true){

            return $this->container->view->render($response, 'register.html.twig', array(
                'validation' => $validation,
                'form' => $val_array,));
        }
        else{
            $userService = $this->container->get('userServices');
            $resp = $userService->registerNewUser($form_data);

            //var_dump($form_data);

            if ($resp['exception']){

                return $this->container->view->render($response, 'userNotification.twig', array(
                    'exception' => true,
                    'message' => $resp['message']));
            }
            else{

                $mailServices = $this->container->get('mailServices');
                $mailResult = $mailServices->sendActivateAccountMail($resp['user'], $request);

                //send e-mail to site admin informing new user registration if enabled
                if ($this->systemInfo['settings']->getEnableNewUserNotification()){
                    $mailRes = $mailServices->sendInformSiteAdminMail($resp['user'], $request);
                }
                
                if ($mailResult['sent']){

                    return $this->container->view->render($response, 'userNotificationMail.twig', array(
                        'exception' => false,
                        'mailResponse' => $mailResult));
                }

                return $this->container->view->render($response, 'userNotificationMail.twig', array(
                    'exception' => false,
                    'mailResponse' => $mailResult));
            }
        }
    }
    
    public function activateAccountAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $userServices = $this->container->get('userServices');
        $result = $userServices->activateAccount($args['key']);

        if ($result['exception']){

            //send e-mail to site admin informing new user registration
           // $mailServices = $this->container->get('mailServices');
            //$mailRes = $mailServices->sendInformSiteAdminMail($result['user'], $request);

            //var_dump($mailRes);
            return $this->container->view->render($response, 'userNotification.twig', array(
                'exception' => $result['exception'],
                'message' => $result['message']));
        }

        session_destroy();
        //create user session
        session_start();
        $_SESSION['user_id'] = $result['user']->getId();
        $_SESSION['user_role'] = $result['user']->getRole();

        $redirect_url = $this->utilsServices->getBaseUrl($request).'/user/home';
        return $response->withRedirect($redirect_url, 200);

    }

    public function resetPasswordAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        //TODO: sanitize params
        $key = $args['key'];
        $userService = $this->container->get('userServices');
        $resp = $userService->findUserByKey($key);

        if ($resp['exception'] == false){
            return $this->container->view->render($response, 'resetPassword.html.twig', array(
                'exception' => false,
                'message' => $resp['message']));
        }
        return $this->container->view->render($response, 'userNotification.twig', array(
            'exception' => true,
            'message' => $resp['message']));
    }

    public function processResetPasswordAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        //TODO: sanitize params
        $key = $args['key'];

        $form_data = $request->getParsedBody();
        $userService = $this->container->get('userServices');
        $resp = $userService->findUserByKey($key);

        if ($resp['exception'] == false){

            $user = $resp['user'];
            $resetResp = $userService->resetPassword($user->getId(), $form_data['password']);

            return $this->container->view->render($response, 'userNotification.twig', array(
                'exception' => $resetResp['exception'],
                'message' => $resetResp['message']));
        }

        return $this->container->view->render($response, 'userNotification.twig',  array(
            'exception' => $resp['exception'],
            'message' => $resp['message']));
    }

    public function updateDataPrivacyPreferencesAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        //TODO: sanitize params
        $key = $args['key'];
        $userService = $this->container->get('userServices');
        $resp = $userService->findUserByKey($key);

        //convert the data to be shown in the form
        foreach ($resp['user'] as $key =>$data){
            $user[$key] = array('value' => $data,
                'error' => false);
        }

        if ($resp['exception'] == false){
            return $this->container->view->render($response, 'updateDataProtectionPreferences.html.twig', array(
                'exception' => false,
                'publicKey' =>  $this->systemInfo['settings']->getReCaptchaPublicKey(),
                'form' => $user,
                'message' => $resp['message']));
        }
        return $this->container->view->render($response, 'userNotification.twig', array(
            'exception' => true,
            'publicKey' =>  $this->systemInfo['settings']->getReCaptchaPublicKey(),
            'message' => $resp['message']));
    }

    public function processUpdateDataPrivacyPreferencesAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        //TODO: sanitize params
        $key = $args['key'];

        $form_data = $request->getParsedBody();

        if ($form_data['reCaptchaActive'] == 'true'){

            $verifyCaptchaResult = $this->utilsServices->verifyRecaptcha($form_data['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

            //var_dump($userInfo['reCaptchaActive']);
            if (!$verifyCaptchaResult->success){

                return $this->container->view->render($response, 'userNotification.twig', array(
                    'exception' => true,
                    'message' => 'Oops... Google thinks you are a robot. Have a nice day'));
            }
        }

        $userService = $this->container->get('userServices');
        $resp = $userService->findUserByKey($key);

        if ($resp['exception'] == false){

            $user = $resp['user'];
            $resetResp = $userService->updateUserPrivacyPreferences($user->getId(), $form_data);

            return $this->container->view->render($response, 'userNotification.twig', array(
                'exception' => $resetResp['exception'],
                'message' => $resetResp['message']));
        }

        return $this->container->view->render($response, 'userNotification.twig',  array(
            'exception' => $resp['exception'],
            'message' => $resp['message']));
    }

    public function getInstitutionOfActiveMembersAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $membersResp = $this->membershipServices->getMembers(
            array('membershipTypeId' => (int)$args['typeId'], 'cancelled' => false), array(), null, true, false, false);

        $i = 0;
        $members = null;
        foreach ($membersResp['members'] as $member){

            $members[$i]['user'] = $member['user'];
            $members[$i]['membershipTypeName'] = $member['membershipTypeName'];
            $members[$i]['email'] = str_replace('@', "(at)", $member['user']->getEmail1());
            $members[$i]['country'] = $this->utilsServices->getCountryNameByCode($member['user']->getCountry());
            $i++;
        }

        return $this->container->view->render($response, 'listMembersPublic.html.twig', array(
            'exception' => $membersResp['exception'],
            'members' => $members));
    }

    public function forgotPasswordAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        return $this->container->view->render($response, 'forgotPassword.html.twig');
    }

    public function processForgotPasswordAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $form_data = $request->getParsedBody();

        $userService = $this->container->get('userServices');
        $resp = $userService->findUserByEmail($form_data['email_1']);
        
        if ($resp['exception'] == false){

            $user = $resp['user'];
            $mailServices = $this->container->get('mailServices');
            $resetResp = $mailServices->sendResetPasswordMail($user, $request);
            return $this->container->view->render($response, 'userNotificationMail.twig', array('mailResponse' => $resetResp));
        }

        return $this->container->view->render($response, 'userNotificationMail.twig', array('mailResponse' => $resp));
    }

    public function publicNewslettersAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {

        $result = $this->userServices->getPublishedNewsletters();
        $result['baseSiteUrl'] = $this->utilsServices->getBaseUrl($request);

        return $this->container->view->render($response, 'publishedNewslettersPublic.html.twig', $result);
    }

    public function paypalIPnAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $parsedBody = $request->getParsedBody();

        //$myfile = fopen('webservice.txt','w') or die("Unable to open file");
        //fwrite($myfile, $request->getBody());
        //fclose($myfile);

        $resp = $this->billingServices->verifyPaypalIpn($parsedBody,  $this->systemInfo['settings']->getPaypalSandboxModeActive());

        if ($resp['exception'] == false AND $resp['verified'] == true){

            //Process actions
            $invoiceId = (int)$resp['paypalVars']['invoice'];
            $amountPaid = $resp['paypalVars']['mc_gross'];

            $paymentGatewayData['txn_id'] = $resp['paypalVars']['txn_id'];
            $paymentGatewayData['payer_id'] = $resp['paypalVars']['payer_id'];
            $paymentGatewayData['payer_email'] = $resp['paypalVars']['payer_email'];
            $paymentGatewayData['receiver_email'] = $resp['paypalVars']['receiver_email'];
            $paymentGatewayData['ipn_track_id'] = $resp['paypalVars']['ipn_track_id'];
            $paymentGatewayData['payment_status'] = $resp['paypalVars']['payment_status'];

            $result = $this->billingServices->addPayment($invoiceId, $amountPaid, null, 'PAYPAL', $paymentGatewayData, NULL, true, $request);

            //TODO: log results;
        }

        return $response->withStatus(200);
    }

    public function publicNewsletterAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $key = $args['key'];
        $baseUrl = $this->utilsServices->getBaseUrl($request);
        $result = $this->userServices->assemblePublicNewsletter($key, false, $baseUrl, true);

        $result['trackingId'] = $this->systemInfo['settings']->getGoogleAnalyticsTrackingId();
        return $this->container->view->render($response, 'newsletter/newsletter.html.twig', $result);
    }

    //This method searches for a user for the provided LinkedIn ID. If it does not find it, searches for a user with the same e-mail address.
    // If one of the searches is successful logs the user in.
    // If not, create a new local account and associate it with the LinkedIn account
    public function linkedInOauth2Action(ServerRequestInterface $request, ResponseInterface $response)
    {
        $params = $request->getQueryParams();
        $req = 'grant_type=authorization_code&code='.$params['code'].'&redirect_uri='.$this->utilsServices->getUrlForRouteName($request, 'linkedInOauth2Redirect').'&client_id='.$this->systemInfo['settings']->getLinkedInClientId().'&client_secret='.$this->systemInfo['settings']->getLinkedInClientSecret();

        try{
            $resp= Request::post('https://www.linkedin.com/oauth/v2/accessToken')
                ->addHeader('Content-Type','application/x-www-form-urlencoded')
                ->body($req)
                ->send();
        }
        catch (Exception $e) {
            return array('exception' => true,
                'verified' => false,
                'message' => $e->getMessage());
        }

        switch ($params['state']){

            case 'login':
                $resp = $resp = $this->linkedInServices->searchLocalAccount($resp->body->access_token);
                if ($resp['exception'] == false){

                    //create user session
                    session_start();
                    $_SESSION['user_id'] = $resp['user']->getId();
                    $_SESSION['user_role'] = $resp['user']->getRole();

                    $redirect_url = $this->utilsServices->getBaseUrl($request).'/user/home';

                    if (isset($_SESSION['orig_uri'])){

                        $redirect_url = $_SESSION['orig_uri'];
                    }
                    //Log user action (login)
                    $context['user_id'] = $_SESSION['user_id'];
                    $context['user_role'] = $_SESSION['user_role'];
                    $context['type'] = SIGN_IN_OUT;
                    $context['identityProvider'] = 'LinkedIn';
                    $this->container->get('userLogger')->info('User logged in', $context);

                    return $response->withRedirect($redirect_url, 200);
                }
            break;
            case 'associate':

                session_start();
                $respUser =  $this->userServices->getUserById($_SESSION['user_id']);

                //var_dump($respUser);
                $linkedInProfileData = $resp = $this->linkedInServices->getLinkedInUserProfileData($resp->body->access_token);
                $assocResp = $this->linkedInServices->associateAccountWithLinkedIn($respUser['user'], $linkedInProfileData['result']);

                $_SESSION['linkedInStatus'] = $assocResp;

                //var_dump($assocResp);

                $redirect_url = $this->utilsServices->getBaseUrl($request).'/user/profile';

                //Log user action (new user registration)
                $this->container->get('userLogger')->info('User enabled sign on with LinkedIn',
                    array('type' => UPDATE_USER,
                          'user_id' => $_SESSION['user_id'],
                          'user_role' => $_SESSION['user_role']
                    ));

                return $response->withRedirect($redirect_url, 200);

                break;
            default:
                $redirect_url = $this->utilsServices->getBaseUrl($request).'/login';
                return $response->withRedirect($redirect_url, 200);
        }
    }

}
