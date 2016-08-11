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

    //Route /api/v1/sendBulkMail
    public function sendBulkMailAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $body = $request->getBody();
        $req = json_decode($body);

        $mailServices = $this->container->get('mailServices');
        $results = $mailServices->sendBulkEmails($req->userIds, $req->mailSubject, $req->mailBody, $req->replyTo, $request);
        
        echo json_encode($results);
    }

    //Route /api/v1/getFilteredUsers
    public function getFilteredUsersAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        /*
         * Example of request body:
         * {
	         "groupe": "users/members",
	         "memberType": "Name of membership type",
	         "memberStatus": "status",
            }
        */

        $body = $request->getBody();
        $body_json = json_decode($body);

        $userService = $this->container->get('userServices');
        $resp = $userService->findUsersFiltered(null);
        
        $result = json_encode($resp['users']);

        $newResponse = $response->withJson($resp['users']);

        return $newResponse;
    }
}
