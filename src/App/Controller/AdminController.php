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
        $this->userServices = $container->get('userServices');
        $this->systemInfo = $this->userServices->getSystemInfo();
    }


    public function usersAction(ServerRequestInterface $request, ResponseInterface $response, $args) {

        $em = $this->container->get('em');
        $users = $em->getRepository('App\Entity\User')->findAll();
        
        return $this->container->view->render($response, 'admin/usersTable.html.twig', array(
            'systemInfo' => $this->systemInfo['settings'],
            'user_role' => $_SESSION['user_role'],
            'user_id' => $_SESSION['user_id'],
            'users' => $users
        ));
    }


    public function viewUserProfileAction(ServerRequestInterface $request, ResponseInterface $response, $args) {


        $userId = $args['userId'];

        $resp =  $this->userServices->getUserById($userId);

        //convert the data to be shown in the form
        foreach ($resp['user'] as $key =>$data){
            $user[$key] = array('value' => $data,
                'error' => false);
        }
        $validation = array('exception' => false,
            'message' => '',
            'fields' => array());

        return $this->container->view->render($response, 'admin/adminEditUser.html.twig', array('systemInfo' => $this->systemInfo['settings'],
                                                                                                'user_role' => $_SESSION['user_role'],
                                                                                                'form_submission' => false,
                                                                                                'exception' => $resp['exception'],
                                                                                                'message' => $resp['message'],
                                                                                                'form' => $user));
    }

    public function saveUserProfileAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {

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
                'systemInfo' => $this->systemInfo['settings'],
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
                'systemInfo' => $this->systemInfo,
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

        $resp = $this->userServices->getUserById($userId);

        $mailServices = $this->container->get('mailServices');
        $resetResp = $mailServices->sendResetPasswordMail($resp['user'], $request);

        return $this->container->view->render($response, 'userNotificationMail.twig', array('systemInfo' => $this->systemInfo['settings'],
                                                                                             'mailResponse' => $resetResp));
    }

    public function createBulkMailAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {

        return $this->container->view->render($response, 'admin/adminWriteBulkMail.html.twig', array(
            'systemInfo' => $this->systemInfo,
            'user_role' => $_SESSION['user_role']));
    }

    public function verifyBulkMailAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $form_data = $request->getParsedBody();

        $mailServices = $this->container->get('mailServices');

        $respMail = $mailServices->highlightPlaceholders($form_data['subject'], $form_data['emailBody']);

        $userService = $this->container->get('userServices');
        $resp = $userService->findUsersFiltered(null);


        return $this->container->view->render($response, 'admin/adminVerifyBulkMail.html.twig', array(
            'systemInfo' => $this->systemInfo,
            'user_role' => $_SESSION['user_role'],
            'users' => $resp['users'],
            'highlightedBody' => $respMail['body'],
            'highlightedSubject' => $respMail['subject'],
            'submited_form' => $form_data));

    }

}