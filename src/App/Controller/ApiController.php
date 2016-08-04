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

    //Route /api/v1/testAction
    public function testAction(ServerRequestInterface $request, ResponseInterface $response)
    {

        $mailServices = $this->container->get('mailServices');
        $results = $mailServices->sendBulkEmails(array(2, 38, 63, 40, 1, 'bla'), 'Test Subject', 'test body..hehe bye!');
        
        echo json_encode($results);
    }

}
