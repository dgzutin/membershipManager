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
        $this->membershipServices = $container->get('membershipServices');
        $this->mailServices = $container->get('mailServices');
        $this->utilsServices = $container->get('utilsServices');
    }


    public function usersAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {

        $users = $this->userServices->findUsersFiltered(array());
        
        return $this->container->view->render($response, 'admin/usersTable.html.twig', array(
            'users' => $users['users']
        ));
    }

    public function registerNewUserAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {

        $val_array = null;
        if ($request->getMethod() == 'POST'){

            $form_data = $request->getParsedBody();
            foreach ($form_data as $key =>$data){

                //TODO: add validation code for each input here
                $val_array[$key] = array('value' => $data,
                    'error' => false);
            }
            $validation = array('exception' => false,
                'message' => 'One or more fields are not valid. Please check your data and submit it again.',
                'fields' => array());

            if ($validation['exception'] == true){

                return $this->container->view->render($response, 'admin/adminRegisterNewUser.html.twig', array(
                    'form_submission' => true,
                    'exception' => true,
                    'message' => $validation['message'],
                    'form' => $val_array));
            }
            else{
                $userService = $this->container->get('userServices');
                $resp = $userService->registerNewUser($form_data);

                if ($resp['exception'] == true){

                    return $this->container->view->render($response, 'admin/adminRegisterNewUser.html.twig', array(
                        'form_submission' => true,
                        'exception' => true,
                        'message' => $resp['message'],
                        'form' => $val_array));
                }
                else{

                    if ($form_data['send_confirmation_mail'] == true){
                        $this->mailServices->sendUserAddedByAdminEmail($resp['user'], $request);
                    }

                    return $this->container->view->render($response, 'admin/adminRegisterNewUser.html.twig', array(
                        'form_submission' => true,
                        'exception' => false,
                        'message' => 'User successfully added. ID: '.$resp['user']->getId(),
                        'form' => $val_array));
                }
            }
        }
        else{

            return $this->container->view->render($response, 'admin/adminRegisterNewUser.html.twig', array(
                'form_submission' => false,
                'exception' => false,
                'message' => 'nope',
                'form' => $val_array));
        }
    }

    public function viewUserProfileAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $userId = $args['userId'];
        $resp =  $this->userServices->getUserById($userId);
        //$resultMemberships = $this->membershipServices->getMembershipsForUser($userId);

        if ($resp['exception'] == false){
            $resetPasswordLink = $request->getUri()->getBaseUrl(). '/resetPassword/'.$resp['user']->getProfileKey();
        }
        else{
            $resetPasswordLink = null;
        }

        //convert the data to be shown in the form
        foreach ($resp['user'] as $key =>$data){
            $user[$key] = array('value' => $data,
                'error' => false);
        }
        $validation = array('exception' => false,
            'message' => '',
            'fields' => array());

        return $this->container->view->render($response, 'admin/adminEditUser.html.twig', array(
            'form_submission' => false,
            'exception' => $resp['exception'],
            'message' => $resp['message'],
            'resetPasswordLink' => $resetPasswordLink,
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
                'form_submission' => true,
                'exception' => $resp['exception'],
                'message' => $resp['message'],
                'form' => $user));

        }
    }

    public function resetPasswordAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $userId = (int)$args['userId'];

        $resp = $this->userServices->getUserById($userId);
        $mailServices = $this->container->get('mailServices');
        $resetResp = $mailServices->sendResetPasswordMail($resp['user'], $request);

        return $this->container->view->render($response, 'userNotificationMail.twig', array('mailResponse' => $resetResp));
    }

    public function createBulkMailUsersAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {

        return $this->container->view->render($response, 'admin/adminWriteBulkMail.html.twig', array(
            'user_role' => $_SESSION['user_role']));
    }

    public function verifyBulkMailUsersAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $form_data = $request->getParsedBody();
        $respMail = $this->mailServices->highlightPlaceholders($form_data['subject'], $form_data['emailBody']);

        return $this->container->view->render($response, 'admin/adminVerifyBulkMail.html.twig', array(
            'highlightedBody' => $respMail['body'],
            'highlightedSubject' => $respMail['subject'],
            'form' => $form_data));

    }

    public function membersAction(ServerRequestInterface $request, ResponseInterface $response, $args) 
    {

        $post_data = $request->getParsedBody();

        //var_dump($post_data);
        
        $resp = $this->utilsServices->processFilterForMembersTable($post_data);

        $membership_filter = $resp['membership_filter'];
        $user_filter = $resp['user_filter'];
        $validity_filter = $resp['ValidityFilter'];
        $filter_form = $resp['filter_form'];

        //get the list of members based on the filter
        $membersResp = $this->membershipServices->getMembers($membership_filter, $user_filter, $validity_filter['validity'], $validity_filter['onlyValid'], $validity_filter['onlyExpired'], $validity_filter['never_validated']);

        if ($membersResp['exception']){

            return $this->container->view->render($response, 'userNotification.twig', array('message' => $membersResp['message']));
        }
        
        return $this->container->view->render($response, 'admin/membersTable.html.twig', array(
            'exception' => $membersResp['exception'],
            'message' => $membersResp['message'],
            'membershipTypes' => $filter_form['membershipTypes'],
            'memberGrades' => $filter_form['memberGrades'],
            'validity' => $filter_form['validity'],
            'members' => $membersResp['members'],
            'form' => $post_data
        ));
    }

    public function memberAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $memberId = (int)$args['memberId'];

        $resp = $this->membershipServices->getMemberByMemberId($memberId);

        $membershipTypesResp = $this->membershipServices->getAllMembershipTypes();
        $memberGradesResp = $this->membershipServices->getAllMemberGrades();

        // get membership types and grades to populate select boxes
        $resp['membershipTypes'] = $membershipTypesResp['membershipTypes'];
        $resp['memberGrades'] = $memberGradesResp['memberGrades'];

        if ($resp['exception']){

            return $this->container->view->render($response, 'userNotification.twig', $resp);
        }
        return $this->container->view->render($response, 'admin/adminEditMembership.html.twig', $resp);
    }


    public function createBulkMailMembersAction(ServerRequestInterface $request, ResponseInterface $response, $args) 
    {
        $post_data = $request->getParsedBody();
        $resp_process_filter = $this->utilsServices->processFilterForMembersTable($post_data);
        $filter_form = $resp_process_filter['filter_form'];

        return $this->container->view->render($response, 'admin/adminWriteBulkMailMembers.html.twig', array(
            'membershipTypes' => $filter_form['membershipTypes'],
            'memberGrades' => $filter_form['memberGrades'],
            'validity' => $filter_form['validity'],
            'form' => $post_data));
    }

    public function verifyBulkMailMembersAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $form_data = $request->getParsedBody();
        $resp_process_filter = $this->utilsServices->processFilterForMembersTable($form_data);
        $filter_form = $resp_process_filter['filter_form'];

        $respMail = $this->mailServices->highlightPlaceholders($form_data['subject'], $form_data['emailBody']);

        return $this->container->view->render($response, 'admin/adminVerifyBulkMailMembers.html.twig', array(
            'highlightedBody' => $respMail['body'],
            'highlightedSubject' => $respMail['subject'],
            'membershipTypes' => $filter_form['membershipTypes'],
            'memberGrades' => $filter_form['memberGrades'],
            'validity' => $filter_form['validity'],
            'form' => $form_data));
    }

    public function manageRenewalsAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {

        $membershipId = (int)$args['membershipId'];
        $renewalsRes = $this->membershipServices->getValiditiesForMembershipId($membershipId);

        return $this->container->view->render($response, 'admin/adminManageRenewals.html.twig', $renewalsRes);

    }



}