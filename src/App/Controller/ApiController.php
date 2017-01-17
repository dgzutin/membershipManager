<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 22.07.16
 * Time: 14:54
 */

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;


class ApiController {
    protected $container;
    //Constructor
    public function __construct($container) {

        $this->container = $container;
        $this->membershipServices = $this->container->get('membershipServices');
        $this->utilsServices = $this->container->get('utilsServices');
        $this->billingServices = $this->container->get('billingServices');

    }
    
    //Route /api/v1/sendSingleMail
    public function sendSingleMailAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {

        /*
         * Example of request body:
         * {
	         "email": "address@mail.com",
	         "name": "Name of Recepient",
	         "subject": "Hello Email",
	         "body": "Here is the message itself"
            }
        */
        $body = $request->getBody();
        $body_json = json_decode($body);
        
        $mailServices = $this->container->get('mailServices');
        $result = $mailServices->sendSingleMail($body_json->email, $body_json->name, $body_json->subject, $body_json->body, 'office@igip.org', 'Name of Organization');

        echo json_encode($result);
        
    }

    //Route /api/v1/sendBulkMailUsers
    public function sendBulkMailUsersAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $body = $request->getBody();
        $req = json_decode($body);

        $mailServices = $this->container->get('mailServices');
        $results = $mailServices->sendBulkEmails($req->userIds, $req->mailSubject, $req->mailBody, $req->replyTo, $request);

        return $response->withJson(array('exception' => false,
                                         'results' => $results));
    }

    //Route /api/v1/sendBulkMail
    public function sendBulkMailMembersAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $body = $request->getBody();
        $req = json_decode($body);

        try{
            $mailServices = $this->container->get('mailServices');
            $results = $mailServices->sendBulkEmailsMembers($req->members, $req->mailSubject, $req->mailBody, $req->replyTo, $request);
        }
        catch (\Exception $e){

            return array('exception' => true,
                         'message' => $e->getMessage());
        }

        return $response->withJson(array('exception' => false,
                                         'results' => $results));
    }


    //Route /api/v1/getFilteredUsers
    public function getFilteredUsersAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        /*
         * Example of request body:
         * {
         * ...
            }
        */

        $body = $request->getBody();
        $body_json = json_decode($body);

        $userService = $this->container->get('userServices');
        $resp = $userService->findUsersFiltered(array());

        $result = json_encode($resp['users']);

        $newResponse = $response->withJson($resp['users']);

        return $newResponse;
    }

    //Route /api/v1/getFilteredMembers
    public function getFilteredMembersAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        /*
         * Example of request body:
         * {
	        ...
            }
        */
        $body = $request->getBody();
        $body_json = json_decode($body);

        $filter = array('membershipTypeId' => $body_json->membershipTypeId,
                        'membershipGrade' => $body_json->membershipGrade,
                        'validity' => $body_json->validity);

        $resp = $this->utilsServices->processFilterForMembersTable((array)$body_json);

        $membership_filter = $resp['membership_filter'];
        $user_filter = $resp['user_filter'];
        $validity_filter = $resp['ValidityFilter'];

        //get the list of members based on the filter
        $membersResp = $this->membershipServices->getMembers($membership_filter, $user_filter, $validity_filter['validity'], $validity_filter['onlyValid'], $validity_filter['onlyExpired'], $validity_filter['never_validated']);


        $newResponse = $response->withJson($membersResp);
        return $newResponse;
    }

    //Route /api/v1/membershipQuickRenew
    public function membershipQuickRenewAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        /*
         * Example of request body:
         * {
         *  "memberhipId": id
            }
        */

        $body = $request->getBody();
        $body_json = json_decode($body);
        $membershipId = (int)$body_json->membershipId;

        $result = $this->membershipServices->addNewMembershipValidity($membershipId, NULL, NULL);

        return $response->withJson($result);
    }

    //Route /api/v1/renewMembership
    public function renewMembershipAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        /*
        * Example of request body:
        * {
        *  "memberhipId": id
         * "from": "mm/dd/yyyy",
         * "until": "mm/dd/yyyy"
           }
       */
        $body = $request->getBody();
        $body_json = json_decode($body);
        $membershipId = (int)$body_json->membershipId;

        try{
            $from = new \DateTime($body_json->from.'T23:59:59');
            $until = new \DateTime($body_json->until.'T23:59:59');
        }
        catch (\Exception $e){

            return  $response->withJson(array('exception' => true,
                                              'message' => 'Could not parse date string. The format should be  MM/DD/YYYY. Renewal not added.'));
        }
        return $response->withJson($this->membershipServices->addNewMembershipValidity($membershipId, $from, $until));
    }

    //Route /api/v1/ deleteValidities
    public function deleteValiditiesAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        /*
       * Example of request body:
       * {
       *  "ids": [1, 2, 3, ..]
          }
      */
        $body_json = json_decode($request->getBody());
        $ids = $body_json->ids;

        $i = 0;
        foreach ($ids as $id){
            $ids[$i] = (int)$id;
            $i++;
        }

        return $response->withJson($this->membershipServices->deleteValidities($ids));
    }

    //Route /api/v1/ deletePayments
    public function deletePaymentsAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        /*
       * Example of request body:
       * {
       *  "ids": [1, 2, 3, ..]
          }
      */
        $body_json = json_decode($request->getBody());
        $ids = $body_json->ids;

        $i = 0;
        foreach ($ids as $id){
            $ids[$i] = (int)$id;
            $i++;
        }

        return $response->withJson($this->billingServices->deletePayments($ids));
    }

}
