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
        $this->billingServices = $container->get('billingServices');
        $this->userServices = $container->get('userServices');
        $this->utilsServices = $container->get('utilsServices');
    }

    public function loginAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        session_start();
        //Authorizes request here
        if (isset($_SESSION['user_id'])){

            $homeUrl = $this->utilsServices->getUrlForRouteName($request, 'homeUser');
            return $response->withRedirect($homeUrl, 200);
        }
        return $this->container->view->render($response, 'login.html.twig');
    }

    public function processLoginAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {

        $userInfo = $request->getParsedBody();
        $userService = $this->container->get('userServices');
        $auth_result = $userService->authenticateUser($userInfo['email'],$password = $userInfo['password'] );

        if ( $auth_result['exception'] == false ){

            session_start();
            $_SESSION['user_id'] = $auth_result['user_id'];
            $_SESSION['user_role'] = $auth_result['user_role'];

            $redirect_url = $this->utilsServices->getBaseUrl($request).'/user/home';

            if (isset($_SESSION['orig_uri'])){

                $redirect_url = $_SESSION['orig_uri'];
            }

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
        session_destroy();

        $uri = $this->utilsServices->getBaseUrl($request).'/login';
        return $response = $response->withRedirect($uri, 200);
    }

    public function registerAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {

        return $this->container->view->render($response, 'register.html.twig');
    }

    public function processRegisterAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $form_data = $request->getParsedBody();
        $val_array = null;
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

            if ($resp['exception'] == true){

                return $this->container->view->render($response, 'userNotification.twig', array(
                    'exception' => true,
                    'message' => $resp['message']));
            }
            else{
                $mailServices = $this->container->get('mailServices');
                $mailServices->sendActivateAccountMail($resp['user'], $request);

                return $this->container->view->render($response, 'userNotificationMail.twig', array(
                    'exception' => false,
                    'mailResponse' => $resp));
            }
        }
    }
    
    public function activateAccountAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $userServices = $this->container->get('userServices');
        $result = $userServices->activateAccount($args['key']);
        return $this->container->view->render($response, 'userNotification.twig', array(
            'exception' => $result['exception'],
            'message' => $result['message']));
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

    public function oauth2Action(ServerRequestInterface $request, ResponseInterface $response)
    {
        $params = $request->getQueryParams();

        $req = 'grant_type=authorization_code&code='.$params['code'].'&redirect_uri=https%3A%2F%2Fiaoe.online-engineering.net%2Foauth%2Fv2%2Fredirect&client_id=86ix8uxlc7mudl&client_secret=4nn0gbMet6QnmruS';

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

        echo json_encode($resp);

        echo $params['code'];
       // echo 'Hallo';
    }

}
