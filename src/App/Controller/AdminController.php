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
    }

    public function homeAction(ServerRequestInterface $request, ResponseInterface $response, $args) {

        $links = array('home' =>  $request->getUri()->withPath($this->container->router->pathFor('homeAdmin')),
                      'logout' => $request->getUri()->withPath($this->container->router->pathFor('logout')),
                      'viewProfile' => $request->getUri()->getBaseUrl(). '/admin/users/'.$_SESSION['user_id']);

        return $this->container->view->render($response, 'admin/adminHome.html.twig', array(
            'links' => $links,
            'user_id' => $_SESSION['user_id'],
            'user_role' => $_SESSION['user_role']
        ));
    }

    public function usersAction(ServerRequestInterface $request, ResponseInterface $response, $args) {

        $links = array('home' =>  $request->getUri()->withPath($this->container->router->pathFor('homeAdmin')),
            'logout' => $request->getUri()->withPath($this->container->router->pathFor('logout')),
            'viewProfile' => $request->getUri()->getBaseUrl(). '/admin/users/'.$_SESSION['user_id']);

        $em = $this->container->get('em');
        $users = $em->getRepository('App\Entity\User')->findAll();
        
        return $this->container->view->render($response, 'admin/usersTable.html.twig', array(
            'links' => $links,
            'user_id' => $_SESSION['user_id'],
            'users' => $users
        ));
    }

    public function users(ServerRequestInterface $request, ResponseInterface $response, $args) {

        $em = $this->container->get('em');
        $users = $em->getRepository('App\Entity\User')->findAll();

        $response->getBody()->write(json_encode($users));

    }

    public function viewUserProfileAction(ServerRequestInterface $request, ResponseInterface $response, $args) {

        $links = array('home' =>  $request->getUri()->withPath($this->container->router->pathFor('homeAdmin')),
            'logout' => $request->getUri()->withPath($this->container->router->pathFor('logout')),
            'viewProfile' => $request->getUri()->getBaseUrl(). '/admin/users/'.$_SESSION['user_id']);

        $userId = $args['userId'];


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

        return $this->container->view->render($response, 'admin/adminEditUser.html.twig', array('links' => $links,
                                                                                                'form_submission' => false,
                                                                                                'exception' => $resp['exception'],
                                                                                                'message' => $resp['message'],
                                                                                                'form' => $user));
    }

    public function saveUserProfileAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $links = array('home' =>  $request->getUri()->withPath($this->container->router->pathFor('homeAdmin')),
            'logout' => $request->getUri()->withPath($this->container->router->pathFor('logout')),
            'viewProfile' => $request->getUri()->getBaseUrl(). '/admin/users/'.$_SESSION['user_id']);

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
                'links' => $links,
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
                'links' => $links,
                'form_submission' => true,
                'exception' => $resp['exception'],
                'message' => $resp['message'],
                'form' => $user));

        }
    }


}