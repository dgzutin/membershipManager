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
    }

    public function loginAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {

        return $this->container->view->render($response, 'login.html.twig');

    }

    public function processLoginAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {

        $userInfo = $request->getParsedBody();

        $path = 'login';

        $userService = $this->container->get('userServices');
        $auth_result = $userService->authenticateUser($userInfo['email'],$password = $userInfo['password'] );

        if ( $auth_result['exception'] == false ){

            session_start();
            $_SESSION['user_id'] = $auth_result['user_id'];
            $_SESSION['user_role'] = $auth_result['user_role'];

            if ($auth_result['user_role'] == 'ROLE_USER'){
                $path = 'homeUser';
            }
            elseif ($auth_result['user_role'] == 'ROLE_ADMIN'){
                $path = 'homeAdmin';
            }

            if (isset($_SESSION['original_route'])){
                $path = $_SESSION['original_route'];
            }
            $uri = $request->getUri()->withPath($this->container->router->pathFor($path));
            return $response = $response->withRedirect($uri, 401);
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

            return $this->container->view->render($response, 'register.html.twig', array('validation' => $validation,
                'form' => $val_array,));
        }
        else{

            $userService = $this->container->get('userServices');
            $resp = $userService->registerNewUser($form_data);

            if ($resp['exception'] == true){

                return $this->container->view->render($response, 'userNotification.twig', $resp);
            }
            else{
                $mailServices = $this->container->get('mailServices');
                $mailServices->sendActivateAccountMail($resp['user'], $request);
                return $this->container->view->render($response, 'userNotification.twig', $resp);
            }

        }
    }
    
    public function activateAccountAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $userServices = $this->container->get('userServices');
        $result = $userServices->activateAccount($args['key']);

        return $this->container->view->render($response, 'userNotification.twig', $result);
        
    }
}
