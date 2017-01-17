<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 25.10.16
 * Time: 16:30
 */
namespace App\Service;


use App\Entity\InvoicePayment;
use \Httpful\Request;
use \Exception;

class BillingServices
{

    public function __construct($container)
    {
        $this->em = $container->get('em');
        $this->userServices = $container->get('userServices');
        $this->membershipServices = $container->get('membershipServices');

        $this->Paypal_sandbox_ipn = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';
        $this->Paypal_ipn = 'https://ipnpb.paypal.com/cgi-bin/webscr';

    }
    
    
    public function verifyPaypalIpn($parsedBody, $sandbox)
    {
        
        
        $req = 'cmd=_notify-validate';
        $get_magic_quotes_exists = false;
        if (function_exists('get_magic_quotes_gpc')) {
            $get_magic_quotes_exists = true;
        }
        foreach ($parsedBody as $key => $value) {
            if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
                $value = urlencode(stripslashes($value));
            } else {
                $value = urlencode($value);
            }
            $req .= "&$key=$value";
        }

        $uri_ipn = $this->Paypal_ipn;
        if ($sandbox == true){
            $uri_ipn = $this->Paypal_sandbox_ipn;
        }

        try{
            $response= Request::post($uri_ipn)
                ->addHeader('User-Agent','PHP-IPN-VerificationScript')
                ->body($req)
                ->send();
        }
        catch (Exception $e) {
            return array('exception' => true,
                         'verified' => false,
                         'message' => $e->getMessage());
        }
        
        if ($response->body == 'VERIFIED'){
            return array('exception' => false,
                         'verified' => true,
                         'paypalVars' => $parsedBody,
                         'message' => 'Payment successfully verified');
        }
        return array('exception' => false,
                    'verified' => false,
                    'paypalVars' => $parsedBody,
                    'message' => 'Payment is invalid');
    }

    public function addPayment($invoiceId, $amountPaid, $note, $paymentMode, $paymentGatewayData)
    {
        //verify if full amount was paid to execute OnPayment Actions
        $invoiceData = $this->userServices->getInvoiceDataForUser($invoiceId, NULL);

        if ($invoiceData['exception'] == false){

            $newOutstandingAmount = $invoiceData['outstandingAmount'] - $amountPaid;
            $invoice = $invoiceData['invoice'];

            if ($newOutstandingAmount <= 0){

                if ($invoice->getActionsExecuted() == false AND $invoice->getOnPaymentActions() != null){

                    //RUN here the on payment actions =========================================

                    $result = $this->processOnPaymentActions($invoice->getOnPaymentActions());

                    if ($result['exception'] == false){

                        $invoice->setActionsExecuted(true);
                        $message = array('exception' => false,
                                         'actionExecuted' => true,
                                         'action' => $result['action'],
                                         'actionResult' => $result['result'],
                                         'actionMessage' => $result['message'],
                                         'newOutstandingAmount' => $newOutstandingAmount);
                    }
                    else{
                        $message = array('exception' => true,
                                         'actionExecuted' => false,
                                         'action' => $result['action'],
                                         'actionResult' => $result['result'],
                                         'message' => $result['message'],
                                         'newOutstandingAmount' => $newOutstandingAmount);
                    }
                    //=========================================================================
                }
                else{
                    $message = array('exception' => false,
                                     'actionExecuted' => false,
                                     'actionResult' => NULL,
                                     'actionMessage' => NULL,
                                     'newOutstandingAmount' => $newOutstandingAmount);
                }

                $invoice->setPaid(true);
                $invoice->setPaidDate(new \DateTime());
                $exception = false;
            }
            else{
                //else do nothing
                $invoice->setPaid(false);
                $exception = false;
                $message = array('exception' => false,
                                 'actionExecuted' => false,
                                 'newOutstandingAmount' => $newOutstandingAmount,
                                 'actionResult' => NULL,
                                 'actionMessage' => NULL);
            }
            
        }
        else{
            return array('exception' => true,
                         'message' => $invoiceData['exception']);
        }

        $newInvoicePayment = new InvoicePayment();
        $newInvoicePayment->setInvoiceId($invoiceId);
        $newInvoicePayment->setDatePaid(new \DateTime());
        $newInvoicePayment->setPaymentNote($note);
        $newInvoicePayment->setAmountPaid($amountPaid);
        $newInvoicePayment->setPaymentMode($paymentMode);
        $newInvoicePayment->setSystemMessage(json_encode($message));
        $newInvoicePayment->setPaymentGatewayData(json_encode($paymentGatewayData));

        
        $this->em->persist($newInvoicePayment);
        try{
            $this->em->flush();
        }
        catch (\Exception $e){
            return array('exception' => true,
                         'message' => $e->getMessage());
        }

        //modify Invoice ------------
        $this->em->persist($invoice);
        try{
            $this->em->flush();
        }
        catch (\Exception $e){
            return array('exception' => true,
                'message' => $e->getMessage());
        }


        return array('exception' => $exception,
                     'message' => $message);
    }

    public function processOnPaymentActions($actions)
    {

        $actionsJson = json_decode($actions);

        if ($actionsJson != null){
            $actionName = $actionsJson->actionName;

            $result = null;
            switch ($actionName){

                case 'addValidityForOnePeriod':

                    $membershipIds = $actionsJson->membershipIds;
                    $i = 0;
                    foreach ($membershipIds as $membershipId){
                        $result[$i] = $this->membershipServices->addNewMembershipValidity($membershipId, NULL, NULL);
                        $i++;
                    }
                    break;
                default:
                    //do nothing

                    break;
            }

            return array('exception' => false,
                        'result' => $result,
                        'action' => $actionsJson,
                        'message' => 'Action(s) executed');
        }
        return array('exception' => true,
                     'result' => null,
                     'action' => $actionsJson,
                     'message' => 'Could not parse OnPaymentActions');
    }

    public function getPaymentsForInvoice($invoiceId)
    {
        //verify if full amount was paid to execute OnPayment Actions
        $invoiceData = $this->userServices->getInvoiceDataForUser($invoiceId, NULL);

        if ($invoiceData['exception'] == true){
            return $invoiceData;
        }

        $repository = $this->em->getRepository('App\Entity\InvoicePayment');

        try{
            $payments = $repository->createQueryBuilder('payment')
                ->select('payment')
                ->where('payment.invoiceId = :invoiceId')
                ->setParameter('invoiceId', $invoiceId)
                ->getQuery()
                ->getResult();
        }
        catch (\Exception $e){
            return array('exception' => true,
                         'message' => $e->getMessage());
        }

        return array('exception' => false,
                     'invoiceData' => $invoiceData,
                     'count' => count($payments),
                     'payments' => $payments);
    }

    public function deletePayments($ids)
    {

        $results = null;
        $i = 0;
        $deletedCount = 0;
        foreach ($ids as $id){

            try{
                $repository = $this->em->getRepository('App\Entity\InvoicePayment');
                $payment = $repository->createQueryBuilder('payment')
                    ->select('payment')
                    ->where('payment.id = :id')
                    ->setParameter('id', $id)
                    ->getQuery()
                    ->getOneOrNullResult();
            }
            catch (\Exception $e){
                $results[$i] = array('exception' => true,
                                     'paymentId' => $id,
                                     'message' => $e->getMessage());
                $payment = -1;
            }

            if (($payment != null) AND ($payment != -1)){
                //delete validity

                try{
                    $this->em->remove($payment);
                    $this->em->flush();

                    $results[$i] = array('exception' => false,
                        'paymentId' => $id,
                        'message' => 'Item successfully deleted');
                    $deletedCount ++;
                }
                catch (\Exception $e){
                    $results[$i] = array('exception' => true,
                        'paymentId' => $id,
                        'message' => $e->getMessage());
                }
            }
            elseif ($payment == null){
                      $results[$i] = array('exception' => true,
                                          'paymentId' => $id,
                                          'message' => 'Payment with id '.$id.' not found');
            }
            $i++;
        }

        if ($deletedCount == 0){

            return array('exception' => true,
                         'results' => $results,
                         'message' => $deletedCount.' item(s) deleted');
        }
        return array('exception' => false,
                     'results' => $results,
                     'message' => $deletedCount.' item(s) deleted');
    }
        
}