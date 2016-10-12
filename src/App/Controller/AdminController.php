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

        $userService = $this->container->get('userServices');
        $resp = $userService->getUserById($_SESSION['user_id']);
        $user = $resp['user'];
        $userName = $user->getFirstName().' '.$user->getLastName();

        return $this->container->view->render($response, 'admin/adminHome.html.twig', array(
            'links' => $links,
            'user_id' => $_SESSION['user_id'],
            'user_role' => $_SESSION['user_role'],
            'userName' => $userName
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
            'user_role' => $_SESSION['user_role'],
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
                                                                                                'user_role' => $_SESSION['user_role'],
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
                'user_role' => $_SESSION['user_role'],
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
                'user_role' => $_SESSION['user_role'],
                'form_submission' => true,
                'exception' => $resp['exception'],
                'message' => $resp['message'],
                'form' => $user));

        }
    }

    public function resetPasswordAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $userId = $args['userId'];

        $userService = $this->container->get('userServices');
        $resp = $userService->getUserById($userId);

        $mailServices = $this->container->get('mailServices');
        $resetResp = $mailServices->sendResetPasswordMail($resp['user'], $request);

        return $this->container->view->render($response, 'userNotification.twig', $resetResp);
    }

    public function createBulkMailAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {

        $links = array('home' =>  $request->getUri()->withPath($this->container->router->pathFor('homeAdmin')),
            'logout' => $request->getUri()->withPath($this->container->router->pathFor('logout')),
            'viewProfile' => $request->getUri()->getBaseUrl(). '/admin/users/'.$_SESSION['user_id']);

        return $this->container->view->render($response, 'admin/adminWriteBulkMail.html.twig', array(
            'links' => $links,
            'user_role' => $_SESSION['user_role']));
    }

    public function verifyBulkMailAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $form_data = $request->getParsedBody();

        $mailServices = $this->container->get('mailServices');

        $respMail = $mailServices->highlightPlaceholders($form_data['subject'], $form_data['emailBody']);

        $userService = $this->container->get('userServices');
        $resp = $userService->findUsersFiltered(null);


        $links = array('home' =>  $request->getUri()->withPath($this->container->router->pathFor('homeAdmin')),
            'logout' => $request->getUri()->withPath($this->container->router->pathFor('logout')),
            'viewProfile' => $request->getUri()->getBaseUrl(). '/admin/users/'.$_SESSION['user_id']);

        return $this->container->view->render($response, 'admin/adminVerifyBulkMail.html.twig', array(
            'links' => $links,
            'user_role' => $_SESSION['user_role'],
            'users' => $resp['users'],
            'highlightedBody' => $respMail['body'],
            'highlightedSubject' => $respMail['subject'],
            'submited_form' => $form_data));

    }

    public function yourMembershipAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $em = $this->container->get('em');
        $repository = $em->getRepository('App\Entity\MembershipType');
        $membershipTypes = $repository->createQueryBuilder('memberships')
            ->select('memberships')
            ->getQuery()
            ->getResult();

        $shoppingCartServices = $this->container->get('shoppingCartServices');
        $items = $shoppingCartServices->getItems();

        $total = $shoppingCartServices->getTotalPrice($items);
        $totalPrice = number_format($total,2,".",",");

        $links = array('home' =>  $request->getUri()->withPath($this->container->router->pathFor('homeAdmin')),
            'logout' => $request->getUri()->withPath($this->container->router->pathFor('logout')),
            'viewProfile' => $request->getUri()->getBaseUrl(). '/admin/users/'.$_SESSION['user_id']);

        return $this->container->view->render($response, 'user/selectMembershipUser.html.twig', array('user_role' => $_SESSION['user_role'],
            'membershipTypes' => $membershipTypes,
            'checkoutUrl' => $request->getUri()->withPath($this->container->router->pathFor('orderSummaryAdmin')),
            'addMembershipToCartUrl' => $request->getUri()->getBaseUrl(). '/admin/addMembershipToCart',
            'items' => $items,
            'currency' => 'EUR',
            'removeItemBaseUrl' => $request->getUri()->getBaseUrl(). '/admin/removeItemfromCart',
            'totalPrice' => $totalPrice,
            'message' => '',
            'form' => ''));
    }

    public function shoppingCartAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $shoppingCartServices = $this->container->get('shoppingCartServices');
        $items = $shoppingCartServices->getItems();

       // shoppingCartServices
        $links = array('home' =>  $request->getUri()->withPath($this->container->router->pathFor('homeAdmin')),
            'logout' => $request->getUri()->withPath($this->container->router->pathFor('logout')),
            'viewProfile' => $request->getUri()->getBaseUrl(). '/admin/users/'.$_SESSION['user_id'],
            'backButton'=> $request->getUri()->withPath($this->container->router->pathFor('yourMembershipAdmin')));

        $total = $shoppingCartServices->getTotalPrice($items);
        $totalPrice = number_format($total,2,".",",");

        return $this->container->view->render($response, 'user/oderSummary.twig', array('links' => $links,
            'user_role' => $_SESSION['user_role'],
            'exception' => false,
            'items' => $items,
            'currency' => 'EUR',
            'removeItemBaseUrl' => $request->getUri()->getBaseUrl(). '/admin/removeItemfromCart',
            'totalPrice' => $totalPrice,
            'message' => ''));
    }

    public function addMembershipToCartAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $membershipTypeId = $args['membershipTypeId'];

        $shoppingCartServices = $this->container->get('shoppingCartServices');
        $res = $shoppingCartServices->addIMembershipToCart($membershipTypeId);

        $uri = $request->getUri()->withPath($this->container->router->pathFor('yourMembershipAdmin'));
        return $response = $response->withRedirect($uri, 200);
    }

    public function removeItemfromCartAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $itemId = $args['itemId'];
        $shoppingCartServices = $this->container->get('shoppingCartServices');
        $resp = $shoppingCartServices->removeItemFromCart($itemId, NULL);

        if ($resp['exception'] == true){

            return $this->container->view->render($response, 'userNotification.twig', $resp);
        }
        $uri = $request->getUri()->withPath($this->container->router->pathFor('yourMembershipAdmin'));
        return $response = $response->withRedirect($uri, 200);

    }



}