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


class PublicController {
    protected $container;
    //Constructor
    public function __construct($container) {

        $this->container = $container;
        $this->systemInfo = $container->get('userServices')->getSystemInfo();
    }

    public function loginAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {

        return $this->container->view->render($response, 'login.html.twig', array('systemInfo' => $this->systemInfo));

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

            $path = $request->getUri()->withPath($this->container->router->pathFor('homeUser'));
            if (isset($_SESSION['original_route'])){
                $path = $path = $request->getUri()->withPath($this->container->router->pathFor($_SESSION['original_route']));

            }

            //var_dump($_SESSION['original_URL']);

            return $response = $response->withRedirect($path, 401);

        }
        
        return $this->container->view->render($response, 'login.html.twig', array(
            'form_submission' => true,
            'systemInfo' => $this->systemInfo,
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

        return $this->container->view->render($response, 'register.html.twig', array('systemInfo' => $this->systemInfo));
    }

    public function processRegisterAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $form_data = $request->getParsedBody();


        foreach ($form_data as $key =>$data){

            //add validation code for each input here

            $val_array[$key] = array('value' => $data,
                                     'error' => false);
        }

        //TODO: Add code to validate form inputs
        $validation = array('exception' => false,
                            'message' => 'One or more fields are not valid. Please check your data and submit it again.',
                            'fields' => array());

        if ($validation['exception'] == true){

            return $this->container->view->render($response, 'register.html.twig', array('systemInfo' => $this->systemInfo,
                                                                                         'validation' => $validation,
                                                                                        'form' => $val_array,));
        }
        else{

            $userService = $this->container->get('userServices');
            $resp = $userService->registerNewUser($form_data);

            if ($resp['exception'] == true){

                return $this->container->view->render($response, 'userNotification.twig', array('exception' => true,
                                                                                                'systemInfo' => $this->systemInfo,
                                                                                                'message' => $resp['message']));
            }
            else{
                $mailServices = $this->container->get('mailServices');
                $mailServices->sendActivateAccountMail($resp['user'], $request);
                return $this->container->view->render($response, 'userNotificationMail.twig', array('exception' => false,
                                                                                                    'systemInfo' => $this->systemInfo,
                                                                                                    'mailResponse' => $resp));
            }
        }
    }
    
    public function activateAccountAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $userServices = $this->container->get('userServices');
        $result = $userServices->activateAccount($args['key']);

        return $this->container->view->render($response, 'userNotification.twig', array('exception' => $result['exception'],
                                                                                        'systemInfo' => $this->systemInfo,
                                                                                        'message' => $result)['message']);
    }

    public function resetPasswordAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        //TODO: sanitize params
        $key = $args['key'];

        $userService = $this->container->get('userServices');
        $resp = $userService->findUserByKey($key);

        if ($resp['exception'] == false){
            return $this->container->view->render($response, 'resetPassword.html.twig', array('exception' => false,
                                                                                              'systemInfo' => $this->systemInfo,
                                                                                              'message' => $resp['message']));
        }
        return $this->container->view->render($response, 'userNotification.twig', array('exception' => true,
                                                                                        'systemInfo' => $this->systemInfo,
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
            return $this->container->view->render($response, 'userNotification.twig', array('exception' => $resetResp['exception'],
                                                                                            'systemInfo' => $this->systemInfo,
                                                                                            'message' => $resetResp['message']));
        }

        return $this->container->view->render($response, 'userNotification.twig',  array('exception' => $resp['exception'],
                                                                                         'systemInfo' => $this->systemInfo,
                                                                                         'message' => $resp['message']));
    }

    public function forgotPasswordAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        return $this->container->view->render($response, 'forgotPassword.html.twig', array('systemInfo' => $this->systemInfo));
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
            return $this->container->view->render($response, 'userNotificationMail.twig', array('systemInfo' => $this->systemInfo,
                                                                                                'mailResponse' => $resetResp));
        }
        return $this->container->view->render($response, 'userNotificationMail.twig', array('systemInfo' => $this->systemInfo,
                                                                                            'mailResponse' => $resp));
    }

    public function paypalIPnAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $paypalVariables = $request->getParsedBody();

        $invoiceId = $paypalVariables['invoice'];
        $paymentStatus = $paypalVariables['payment_status'];
        

    }

}
