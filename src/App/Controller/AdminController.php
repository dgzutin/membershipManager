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
        $this->systemInfo = $this->userServices->getSystemInfo();
    }


    public function usersAction(ServerRequestInterface $request, ResponseInterface $response, $args) {

        $em = $this->container->get('em');
        $users = $em->getRepository('App\Entity\User')->findAll();
        
        return $this->container->view->render($response, 'admin/usersTable.html.twig', array(
            'systemInfo' => $this->systemInfo,
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

        return $this->container->view->render($response, 'admin/adminEditUser.html.twig', array('systemInfo' => $this->systemInfo,
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

        return $this->container->view->render($response, 'userNotificationMail.twig', array('systemInfo' => $this->systemInfo,
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

    public function membersAction(ServerRequestInterface $request, ResponseInterface $response, $args) {

        $post_data = $request->getParsedBody();

        //sanitize post variables, just to be sure
        if (isset($post_data['membershipTypeId'])){
            $filter['membershipTypeId'] = (int)$post_data['membershipTypeId'];
        }
        if (isset($post_data['membershipGrade'])){
            $filter['membershipGrade'] = (int)$post_data['membershipGrade'];
        }

        if (!isset($post_data['validity'])){
            $validity = -1;
        }
        else{
            $validity = $post_data['validity'];
        }

        $now = new \DateTime();
        $current_year = $now->format('Y');

        switch ($validity){
            case null:
                $onlyValid = false;
                $onlyexpired = false;
                $never_validated = false;
                $validity = null;
                break;
            case -1:
                $onlyValid = false;
                $onlyexpired = false;
                $never_validated = false;
                $validity = null;
                break;
            case 0:
                $onlyValid = true;
                $onlyexpired = false;
                $never_validated = false;
                $validity = null;
                break;
            case 1:
                $onlyValid = false;
                $onlyexpired = false;
                $never_validated = false;
                $validity = new \DateTime($current_year.'-12-31T23:59:59');
                break;
            case 2:
                $onlyValid = false;
                $onlyexpired = false;
                $never_validated = false;
                $validity = new \DateTime($current_year.'-12-31T23:59:59');
                $validity->add(new \DateInterval('P1Y'));
                break;
            case 3:
                $onlyValid = false;
                $onlyexpired = false;
                $never_validated = false;
                $validity = new \DateTime($current_year.'-12-31T23:59:59');
                $validity->sub(new \DateInterval('P1Y'));
                break;
            case 4:
                $onlyValid = false;
                $onlyexpired = false;
                $never_validated = false;
                $validity = new \DateTime($current_year.'-12-31T23:59:59');
                $validity->sub(new \DateInterval('P2Y'));
                break;
            case 5:
                $onlyValid = false;
                $onlyexpired = true;
                $never_validated = false;
                $validity = null;
                break;
            case 6:
                $onlyValid = false;
                $onlyexpired = false;
                $never_validated = true;
                $validity = null;
                break;
            default:
                $onlyValid = false;
                $onlyexpired = false;
                $never_validated = false;
                $validity = null;
                break;
        }

        $data = array();
        if (isset($post_data['membershipTypeId']) or isset($post_data['membershipGrade'])){

            foreach ($filter as $key => $param){

                if ($param != -1){
                    $data[$key] = $param;
                }
            }
        }

        // get data to generate the filter options
        $membershipTypesResp = $this->membershipServices->getAllMembershipTypes();
        $memberGradesResp = $this->membershipServices->getAllMemberGrades();

        //Currently only YEAR is supported
        $recurrence = 'year';
        switch ($recurrence){

            case 'year':
                $now = new \DateTime();
                $current_year = $now->format('Y');
                $current_period = new \DateTime($current_year.'-12-31T23:59:59');
                $validity_filter['current_period'] = $current_period->format('jS F Y');
                $next_period = $current_period->add(new \DateInterval('P1Y'));
                $validity_filter['next_period'] = $next_period->format('jS F Y');
                $one_period_ago = $current_period->sub(new \DateInterval('P2Y'));
                $validity_filter['one_period_ago'] = $one_period_ago->format('jS F Y');
                $two_periods_ago = $current_period->sub(new \DateInterval('P1Y'));
                $validity_filter['two_periods_ago'] = $two_periods_ago->format('jS F Y');
                break;
        }

        $membersResp = $this->membershipServices->getMembers($data, $validity, $onlyValid, $onlyexpired, $never_validated);
        
        if ($membersResp['exception']){

            return $this->container->view->render($response, 'userNotification.twig', array('systemInfo' => $this->systemInfo,
                                                                                            'message' => $membersResp['message']));
        }

        return $this->container->view->render($response, 'admin/membersTable.html.twig', array(
            'systemInfo' => $this->systemInfo,
            'membershipTypes' => $membershipTypesResp['membershipTypes'],
            'memberGrades' => $memberGradesResp['memberGrades'],
            'validity_filter' => $validity_filter,
            'user_role' => $_SESSION['user_role'],
            'members' => $membersResp['members'],
            'form' => $post_data
        ));
    }

}