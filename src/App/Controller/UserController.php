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

        return $this->container->view->render($response, 'user/userHome.html.twig', array(
            'links' => $links,
            'user_id' => $_SESSION['user_id'],
            'user_role' => $_SESSION['user_role']
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
                                                                                                     'form_submission' => true,
                                                                                                     'exception' => $resp['exception'],
                                                                                                     'message' => $resp['message'],
                                                                                                     'form' => $user));

        }
    }

}