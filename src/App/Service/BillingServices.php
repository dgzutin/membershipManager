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

    public function addPayment($invoiceId, $amountPaid, $note, $paymentMode, $paypalVars)
    {
        //verify if full amount was paid to execute OnPayment Actions
        $invoiceData = $this->userServices->getInvoiceDataForUser($invoiceId, NULL);

        if ($invoiceData['exception'] == false){

            $newOutstandingAmount = $invoiceData['outstandingAmount'] - $amountPaid;
            $invoice = $invoiceData['invoice'];

            if ($newOutstandingAmount <= 0){

                if ($invoice->getActionsExecuted() == false AND $invoice->getOnPaymentActions() != null){

                    //RUN here the on payment actions =========================================






                    //=========================================================================
                    $invoice->setActionsExecuted(true);
                    $message = 'New payment was saved and actions were performed. The outstanding amount is '.$invoiceData['invoiceCurrency'].' '.$newOutstandingAmount;
                }
                else{
                    $message = 'New payment was saved, no action(s) executed. The outstanding amount is '.$invoiceData['invoiceCurrency'].' '.$newOutstandingAmount;
                }

                $invoice->setPaid(true);
                $invoice->setPaidDate(new \DateTime());
                $exception = false;
            }
            else{
                //else do nothing
                $invoice->setPaid(false);
                $exception = false;
                $message = 'New payment was saved, no action(s) executed. The outstanding amount is '.$invoiceData['invoiceCurrency'].' '.$newOutstandingAmount;
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
        $newInvoicePayment->setSystemMessage($message);

        if ($paypalVars != null){
            $newInvoicePayment->setPaypalTransactionId($paypalVars['txn_id']);
            $newInvoicePayment->setPaypalPayerId($paypalVars['payer_id']);
            $newInvoicePayment->setPaypalReceiverEmail($paypalVars['receiver_email']);
            $newInvoicePayment->setPaypalIpnTrackId($paypalVars['ipn_track_id']);
            $newInvoicePayment->setPaypalPaymentStatus($paypalVars['payment_status']);
        }

        
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
        
}