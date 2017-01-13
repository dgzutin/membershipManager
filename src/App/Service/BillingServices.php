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

        if ($sandbox == true){
            $uri_ipn = $this->Paypal_sandbox_ipn;
        }
        else{
            $uri_ipn = $this->Paypal_ipn;
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
                         'paypalVars' => $parsedBody);
        }
        return array('exception' => false,
                    'verified' => false,
                    'paypalVars' => $parsedBody);
    }

    public function addPayment($invoiceId, $amountPaid, $note, $paymentMode, $paypalVars)
    {

        $newInvoicePayment = new InvoicePayment();
        $newInvoicePayment->setInvoiceId($invoiceId);
        $newInvoicePayment->setDatePaid(new \DateTime());
        $newInvoicePayment->setPaymentNote($note);
        $newInvoicePayment->setAmountPaid($amountPaid);
        $newInvoicePayment->setPaymentMode($paymentMode);

        if ($paypalVars != null){
            $newInvoicePayment->setPaypalTransactionId($paypalVars['txn_id']);
            $newInvoicePayment->setPaypalPayerId($paypalVars['payer_id']);
            $newInvoicePayment->setPaypalReceiverEmail($paypalVars['receiver_email']);
            $newInvoicePayment->setPaypalIpnTrackId($paypalVars['ipn_track_id']);
        }

        
        $this->em->persist($newInvoicePayment);
        try{
            $this->em->flush();
        }
        catch (\Exception $e){
            return array('exception' => true,
                         'message' => $e->getMessage());
        }

        return array('exception' => false,
                     'message' => 'New payment was saved');
        
    }
        
}