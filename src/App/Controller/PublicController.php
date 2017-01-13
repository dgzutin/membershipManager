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
    }

    public function loginAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {

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

            $redirect_url = $request->getUri()->withPath($this->container->router->pathFor('homeUser'));
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
        $uri = $request->getUri()->withPath($this->container->router->pathFor('login'));
        return $response = $response->withRedirect($uri);
    }

    public function logoutAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        session_start();
        session_destroy();

        $uri = $request->getUri()->withPath($this->container->router->pathFor('login'));
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
        //$paypalVariables = $request->getParsedBody();

        $myfile = fopen('webservice.txt','w') or die("Unable to open file");
        fwrite($myfile, $request->getBody());
        fclose($myfile);

        echo '';

        

        $uri_sandbox = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';
        $uri = 'https://ipnpb.paypal.com/cgi-bin/webscr';

        //verify IPN
        $verify_body = 'cmd=_notify-validate&'.$request->getBody();

        try{
            $response= Request::post($uri_sandbox)
                ->addHeader('User-Agent','PHP-IPN-VerificationScript')
                ->body($verify_body)
                ->send();
        }
        catch (Exception $e) {
            return array('is_Exception' => true,
                'error_message' => $e->getMessage());
        }


        //$invoiceId = $paypalVariables['invoice'];
        //$paymentStatus = $paypalVariables['payment_status'];

        $myfile = fopen('ipnver.txt','w') or die("Unable to open file");
        fwrite($myfile, $response->raw_body);
        fclose($myfile);
        //var_dump('test');
    }

}
