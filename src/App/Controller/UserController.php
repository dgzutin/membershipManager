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

        $userService = $this->container->get('userServices');
        $resp = $userService->getUserById($_SESSION['user_id']);
        $user = $resp['user'];
        $userName = $user->getFirstName().' '.$user->getLastName();

        return $this->container->view->render($response, 'user/userHome.html.twig', array(
            'links' => $links,
            'user_id' => $_SESSION['user_id'],
            'user_role' => $_SESSION['user_role'],
            'userName' => $userName
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

    public function resetPasswordAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $userId = $_SESSION['user_id'];

        $userService = $this->container->get('userServices');
        $resp = $userService->getUserById($userId);

        $mailServices = $this->container->get('mailServices');
        $resetResp = $mailServices->sendResetPasswordMail($resp['user'], $request);

        return $this->container->view->render($response, 'userNotification.twig', $resetResp);
    }

    public function yourMembershipAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $em = $this->container->get('em');
        $repository = $em->getRepository('App\Entity\MembershipType');
        $membershipTypes = $repository->createQueryBuilder('memberships')
            ->select('memberships')
            ->where('memberships.selectable = :selectable')
            ->setParameter('selectable', true)
            ->getQuery()
            ->getResult();
        
        $links = array('home' =>  $request->getUri()->withPath($this->container->router->pathFor('homeUser')),
            'viewProfile' => $request->getUri()->withPath($this->container->router->pathFor('userProfile')));

        return $this->container->view->render($response, 'user/selectMembershipUser.html.twig', array('links' => $links,
            'membershipTypes' => $membershipTypes,
            'checkoutUrl' => $request->getUri()->withPath($this->container->router->pathFor('shoppingCartUser')),
            'addMembershipToCartUrl' => $request->getUri()->getBaseUrl(). '/user/addMembershipToCart',
            'message' => '',
            'form' => ''));
    }


    public function shoppingCartAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $shoppingCartServices = $this->container->get('shoppingCartServices');
        $items = $shoppingCartServices->getItems();

        // shoppingCartServices
        $links = array('home' =>  $request->getUri()->withPath($this->container->router->pathFor('homeUser')),
            'viewProfile' => $request->getUri()->withPath($this->container->router->pathFor('userProfile')));

        $totalPrice = $shoppingCartServices->getTotalPrice($items);

        return $this->container->view->render($response, 'user/shoppingCart.html.twig', array('links' => $links,
            'exception' => false,
            'items' => $items,
            'currency' => 'EUR',
            'totalPrice' => $totalPrice,
            'message' => ''));
    }

    public function addMembershipToCartAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $membershipTypeId = $args['membershipTypeId'];

        $shoppingCartServices = $this->container->get('shoppingCartServices');
        $res = $shoppingCartServices->addIMembershipToCart($membershipTypeId);
              

        $uri = $request->getUri()->withPath($this->container->router->pathFor('yourMembershipUser'));
        return $response = $response->withRedirect($uri, 200);
    }

    public function registerMemberAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        //TODO: verify if user is already a member

        $userId = $_SESSION['user_id'];
        $userService = $this->container->get('userServices');
        $resp = $userService->getUserById($userId);

        if ($resp['exception'] == false){

            $links = array('home' =>  $request->getUri()->withPath($this->container->router->pathFor('homeUser')),
                'viewProfile' => $request->getUri()->withPath($this->container->router->pathFor('userProfile')));

            return $this->container->view->render($response, 'user/registerMember.html.twig', array('links' => $links,
                'membershipSelected' => $args['selected'],
                'user' => $resp['user'],
                'form' => ''));
        }
        else{
            return $this->container->view->render($response, 'userNotification.twig', $resp);
        }
    }
        

}