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
        $this->userServices = $this->container->get('userServices');
        $this->mailServices = $this->container->get('mailServices');

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

    //Route /api/v1/sendNewsletter
    public function sendNewsletterAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $req = json_decode($request->getBody());

        $baseUrl = $this->utilsServices->getBaseUrl($request);
        $newsletterRes = $this->userServices->assemblePublicNewsletter($req->key, false, $baseUrl);

        if ($newsletterRes['exception'] == true){
            return $response->withJson($newsletterRes);
        }
        $htmlNewsletter = $this->mailServices->createHtmlNewsletter($newsletterRes);

        try{
            $results = $this->mailServices->sendGenericMassMailMembers($req->members,  $newsletterRes['newsletter']->getTitle(), $htmlNewsletter, $req->replyTo, $request);
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

    //Route /api/v1/ addPayment
    public function addPaymentAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        /*
       * Example of request body:
       * {
       *  "invoiceId": 1,
       *  "note": ".....",
       *  "amount": 1.55,
         * datePaid: MM/DD/YYYY
       *  "paymentMode": PAYPAL
          }
      */
        $body_json = json_decode($request->getBody());

        if ($body_json != null){

            $invoiceId = (int)$body_json->invoiceId;
            $amountPaid = (double)$body_json->amountPaid;

            if ($amountPaid == 0){
                return $response->withJson(array('exception' => true,
                    'message' => 'Amount cannot be '.$amountPaid));
            }
            $note = $body_json->note;
            $paymentMode = $body_json->paymentMode;
            $datePaid = $body_json->datePaid;
            $sendReceipt = (bool)$body_json->sendReceipt;

            //var_dump($amountPaid);
            $result = $this->billingServices->addPayment($invoiceId, $amountPaid, $note, $paymentMode, NULL, $datePaid, $sendReceipt, $request);

            return $response->withJson($result);
        }

        return $response->withJson(array('exception' => true,
                                         'message' => 'Could not parse request body'));
    }
    //Route /api/v1/assignArticlesToNewsletter
    public function assignArticlesToNewsletterAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        /*
        * Example of request body:
        * {
         * "assign": true / false
        *  "newsletterId": 1,
        *  "articleIds": [1, 2, ..],
             }
        */
        
        $req_json = json_decode($request->getBody());

        if ($req_json != null){

            $assign = $req_json->assign;
            $assignResult = $this->userServices->assignRemoveArticlesOfNewsletter($req_json->newsletterId, $req_json->articleIds, $assign);
        }
        else{
            $assignResult = array('exception' => true,
                'url' => NULL,
                'message' => 'Invalid JSON');
        }

        return $response->withJson($assignResult);
    }

    //Route /api/v1/deleteNewsletterArticle
    public function deleteNewsletterArticleAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        /*
         * {
         * "articleId": [id]
         * }
         */
        $req_json = json_decode($request->getBody());

        if ($req_json != null){
            $resp = $this->userServices->deleteNewsletterArticle($req_json->articleIds);

            return $response->withJson($resp);
        }
        return $response->withJson(array('exception' => true,
            'message' => 'Could not parse Json request'));
    }

    //Route /api/v1/deleteNewsletter
    public function deleteNewsletterAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        /*
        * {
        * "newsletterId": id,
         *"deleteArticles" true/false
        * }
        */
        $req_json = json_decode($request->getBody());

        if ($req_json != null){
            $resp =  $this->userServices->deleteNewsletter($req_json->newsletterId, $req_json->deleteArticles);

            return $response->withJson($resp);
        }
        return $response->withJson(array('exception' => true,
            'message' => 'Could not parse Json request'));

    }

    //Route /api/v1/deleteMembershipType
    public function deleteMembershipTypeAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        /*
       * {
       * "typeId": id
       * }
       */

        $param = json_decode($request->getBody());

        //var_dump($param);

        $deleteRes = $this->membershipServices->deleteMembershipType((int)$param->typeId);

        return $response->withJson($deleteRes);
    }

    //Route /api/v1/deleteInvoices
    public function deleteInvoicesAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        /*
       * {
       * "invoiceIds": id
       * }
       */
        $param = json_decode($request->getBody());
        $invoicesDeleteResp = $this->billingServices->deleteInvoiceItemsPayments($param->invoiceIds);
        return $response->withJson($invoicesDeleteResp);
    }

    //Route /api-user/v1/saveImage
    public function saveImageAction(ServerRequestInterface $request, ResponseInterface $response)
    {

        $req_json = json_decode($request->getBody());

        if ($req_json != null){

            $imageData = $req_json->imageData;
            $image_content = base64_decode(str_replace("data:image/png;base64,","",$imageData)); // remove "data:image/png;base64,"

            if ($req_json->imageFileName == NULL){
                $imageFileName = sha1(microtime().rand()).'.png';
            }
            else{
                $imageFileName = $req_json->imageFileName;
            }

            try{
                $myfile = fopen('files/newsletter/uploads/'.$imageFileName,'w');
                fwrite($myfile, $image_content);
                fclose($myfile);
            }
            catch(\Exception $e){

                return $response->withJson(array('exception' => true,
                    'url' => null,
                    'message' => $e->getMessage()));
            }

            $resp = array('exception' => false,
                'url' => $this->utilsServices->getBaseUrl($request).'/files/newsletter/uploads/'.$imageFileName,
                'imageFileName' => $imageFileName,
                'message' => 'Image saved');
        }
        else{
            $resp = array('exception' => true,
                          'url' => NULL,
                          'message' => 'Invalid JSON');
        }

        return $response->withJson($resp);
    }

    

}
