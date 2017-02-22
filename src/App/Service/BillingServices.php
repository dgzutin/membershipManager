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
        $this->mailServices = $container->get('mailServices');

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

    public function addPayment($invoiceId, $amountPaid, $note, $paymentMode, $paymentGatewayData, $sendReceiptIfFullPayment = false, $request = null)
    {
        //try to find invoice
        $repository = $this->em->getRepository('App\Entity\Invoice');
        $inv = $repository->createQueryBuilder('inv')
            ->select('inv')
            ->where('inv.id = :id')
            ->setParameter('id', $invoiceId)
            ->getQuery()
            ->getOneOrNullResult();

        $sendReceiptResp = null;
        if ($inv != null){

            //add payment
            $newInvoicePayment = new InvoicePayment();
            $newInvoicePayment->setInvoiceId($invoiceId);
            $newInvoicePayment->setDatePaid(new \DateTime());
            $newInvoicePayment->setPaymentNote($note);
            $newInvoicePayment->setAmountPaid($amountPaid);
            $newInvoicePayment->setPaymentMode($paymentMode);

            $this->em->persist($newInvoicePayment);
            try{
                $this->em->flush();
            }
            catch (\Exception $e){
                return array('exception' => true,
                    'message' => $e->getMessage());
            }

            //verify if full amount was paid to execute OnPayment Actions
            $invoiceData = $this->userServices->getInvoiceDataForUser($invoiceId, NULL);

            $invoice = $invoiceData['invoice'];

            if ($invoiceData['outstandingAmount'] <= 0){

                //send receipt to user if full amount is paid
                if ($sendReceiptIfFullPayment){
                    $sendReceiptResp = $this->mailServices->sendInvoiceToUser($invoiceId, $invoice->getUserId(), $request);
                }

                if ($invoice->getActionsExecuted() == false AND $invoice->getOnPaymentActions() != null){

                    //RUN here the on payment actions =========================================

                    $result = $this->processOnPaymentActions($invoice->getOnPaymentActions());

                    if ($result['exception'] == false){

                        $invoice->setActionsExecuted(true);
                        $actionProtocol = array('exception' => false,
                                         'actionExecuted' => true,
                                         'actionResult' => $result['results'],
                                         'sendReceiptResp' => $sendReceiptResp);
                    }
                    else{
                        $actionProtocol = array('exception' => true,
                                         'actionExecuted' => false,
                                         'actionResult' => $result['results'],
                                         'sendReceiptResp' => $sendReceiptResp);
                    }
                    //=========================================================================
                }
                else{
                    $actionProtocol = array('exception' => false,
                                     'actionExecuted' => false,
                                     'actionResult' => NULL,
                                     'sendReceiptResp' => $sendReceiptResp);
                }

                $invoice->setPaid(true);
                $invoice->setPaidDate(new \DateTime());
                $exception = false;

            }
            else{
                //else do nothing
                $invoice->setPaid(false);
                $exception = false;
                $actionProtocol = array('exception' => false,
                                 'actionExecuted' => false,
                                 'actionResult' => NULL,
                                 'sendReceiptResp' => $sendReceiptResp);
            }
            
        }
        else{
            return array('exception' => true,
                         'message' => 'Invoice not found');
        }

        //save actions now
        $newInvoicePayment->setSystemMessage(json_encode($actionProtocol));
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
                     'currency' => $invoiceData['invoiceCurrency'],
                     'paymentId' => $newInvoicePayment->getId(),
                     'amountPaid' => $newInvoicePayment->getAmountPaid(),
                     'paymentMode' => $newInvoicePayment->getPaymentMode(),
                     'datePaid' => $newInvoicePayment->getDatePaid()->format('jS F Y'),
                     'systemMessage' => $newInvoicePayment->getSystemMessage(),
                     'paymentGatewayData' => $newInvoicePayment->getPaymentGatewayData(),
                     'paymentNote' => $newInvoicePayment->getPaymentNote(),
                     'newOutstandingAmount' => $invoiceData['outstandingAmount'],
                     'actionProtocol' => $actionProtocol,
                     'sendReceiptResp' => $sendReceiptResp,
                     'message' => 'A new payment was added');
    }

    public function processOnPaymentActions($actions)
    {

        $actionsJson = json_decode($actions);

        if ($actionsJson != null){

            $i = 0;
            $results = null;
            foreach ($actionsJson as $action){

                $actionName = $action->actionName;
                switch ($actionName){

                    case 'renewForOnePeriod':

                        $membershipIds = $action->membershipIds;
                        $j = 0;
                        $actionsResults = NULL;
                        foreach ($membershipIds as $membershipId){
                            $actionsResults[$j] = $this->membershipServices->addNewMembershipValidity($membershipId, NULL, NULL);
                            $j++;
                        }

                        break;
                    default:
                        //do nothing

                        $actionsResults = null;
                        break;
                }
                $results[$i] = array('actionName' => $actionName,
                                     'result' => $actionsResults);
                $i++;
            }
            return array('exception' => false,
                         'actions' => $actionsJson,
                         'results' => $results);
        }
        return array('exception' => true,
                     'results' => null,
                     'actions' => $actionsJson,
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
                        'message' => 'Payment successfully deleted');
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

    public function deleteInvoiceItems($ids)
    {
        $results = null;
        $i = 0;
        $deletedCount = 0;
        foreach ($ids as $id){

            $repository = $this->em->getRepository('App\Entity\InvoiceItem');
            $item = $repository->createQueryBuilder('item')
                ->select('item')
                ->where('item.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getOneOrNullResult();

            if ($item != null){

                try{
                    $this->em->remove($item);
                    $this->em->flush();

                    $results[$i] = array('exception' => false,
                        'itemId' => $id,
                        'message' => 'Invoice item successfully deleted');
                    $deletedCount ++;
                }
                catch (\Exception $e){
                    $results[$i] = array('exception' => true,
                        'itemId' => $id,
                        'message' => $e->getMessage());
                }
            }
            else{
                $results[$i] = array('exception' => true,
                    'itemId' => $id,
                    'message' => 'Invoice item with id '.$id.' not found');
            }
            $i++;
        }

        if ($deletedCount == 0){

            return array('exception' => true,
                'results' => $results,
                'message' => $deletedCount.' invoice item(s) deleted');
        }
        return array('exception' => false,
            'results' => $results,
            'message' => $deletedCount.' invoice item(s) deleted');

    }

    public function deleteInvoiceItemsPayments($ids)
    {
        $results = null;
        $i = 0;
        $deletedCount = 0;
        foreach ($ids as $id){

            $invoice = $this->userServices->getInvoiceDataForUser($id, NULL);

            if ($invoice['exception'] == false){

                //delete payments for invoice
                $k = 0;
                $paymentIds = null;
                foreach ($invoice['payments'] as $payment){

                    $paymentIds[$k] = $payment->getId();
                    $k++;
                }

                $deletePaymentResults = null;
                if ($paymentIds != null){
                    $deletePaymentResults = $this->deletePayments($paymentIds);
                }

                //delete items of invoice
                $j = 0;
                $itemIds = null;
                foreach ($invoice['invoiceItems'] as $invoiceItem){

                    $itemIds[$j] = $invoiceItem->getId();
                    $j++;
                }

                $itemDeleteResults = null;
                if ($itemIds != null){

                    $itemDeleteResults = $this->deleteInvoiceItems($itemIds);

                }

                try{
                    $this->em->remove($invoice['invoice']);
                    $this->em->flush();

                    $results[$i] = array('exception' => false,
                        'invoiceId' => $id,
                        'items' => $itemDeleteResults,
                        'payments' => $deletePaymentResults,
                        'message' => 'Invoice '.$id.' successfully deleted');
                    $deletedCount ++;
                }
                catch (\Exception $e){
                    $results[$i] = array('exception' => true,
                        'invoiceId' => $id,
                        'message' => $e->getMessage());
                }

            }
            else{
                $results[$i] = array('exception' => true,
                    'invoiceId' => $id,
                    'message' => 'Invoice with id '.$id.' not found');
            }
            $i++;
        }

        if ($deletedCount == 0){

            return array('exception' => true,
                'results' => $results,
                'message' => $deletedCount.' invoice(s) deleted');
        }
        return array('exception' => false,
            'results' => $results,
            'message' => $deletedCount.' invoice(s) deleted');

    }
        
}