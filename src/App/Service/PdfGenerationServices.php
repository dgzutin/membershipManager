<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 25.10.16
 * Time: 16:30
 */
namespace App\Service;

use TCPDF;

class PdfGenerationServices
{

    public function __construct($container)
    {
        $this->em = $container->get('em');
        $this->userServices = $container->get('userServices');
        $this->membershipServices = $container->get('membershipServices');
        $this->mailServices = $container->get('mailServices');
        $this->utilsServices = $container->get('utilsServices');

    }

    public function generatePdfInvoice($invoiceId, $userId, $request)
    {

        $invoiceData = $this->userServices->getInvoiceDataForUser($invoiceId, $userId, NULL);

        if ($invoiceData['exception'] == true){
            return $invoiceData;
        }

        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document (meta) information
        $pdf->SetCreator($invoiceData['issuerData']['nameOfOrganization']);
        $pdf->SetAuthor($invoiceData['issuerData']['nameOfOrganization']);
        $pdf->SetTitle('Invoice Nr. '.$invoiceData['invoice']->getId());
        $pdf->SetSubject('Invoice Nr. '.$invoiceData['invoice']->getId());

// add a page
        $pdf->AddPage();

        $pdf->setJPEGQuality(90);
        $pdf->Image('assets/images/pdf_invoice/header.jpg', 20, 15, 170, 0, 'JPG', '');

// create address box of invoice issuer
        $pdf = $this->CreateTextBox($pdf, $invoiceData['issuerData']['nameOfOrganization'], 0, 40, 120, 10, 10, 'B');
        $pdf = $this->CreateTextBox($pdf,  $invoiceData['issuerData']['street'], 0, 45, 80, 10, 10);
        $pdf = $this->CreateTextBox($pdf,  $invoiceData['issuerData']['zip'].' '. $invoiceData['issuerData']['city'].' - '. $invoiceData['issuerData']['country'], 0, 50, 80, 10, 10);
        $pdf = $this->CreateTextBox($pdf,  $invoiceData['issuerData']['vat'].' | '. $invoiceData['issuerData']['registrationNumber'], 0, 55, 80, 10, 10);

// create address box
        $pdf = $this->CreateTextBox($pdf, 'BILLING ADDRESS::', 0, 70, 80, 10, 10, 'B');
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getBillingName(), 0, 75, 80, 10, 10);
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getBillingInstitution(), 0, 80, 80, 10, 10);
//$pdf->CreateTextBox($member['address1'], 0, 65, 80, 10, 10);
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getBillingStreet().' - '.$invoiceData['invoice']->getBillingzip().' '.$invoiceData['invoice']->getBillingCity(), 0, 85, 80, 10, 10);
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getBillingCountry(), 0, 90, 80, 10, 10);
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getBillingVat(), 0, 95, 80, 10, 10);
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getBillingReference(), 0, 100, 80, 10, 10);

// invoice title / number
        $pdf = $this->CreateTextBox($pdf, 'Invoice #'.$invoiceData['invoiceId'], 0, 105, 120, 20, 16);

        // date, order ref
        $pdf = $this->CreateTextBox($pdf, 'Date Issued: '.$invoiceData['invoiceDate'], 0, 100, 0, 10, 10, '', 'L');
        $pdf = $this->CreateTextBox($pdf, 'Date due: '.$invoiceData['invoiceDueDate'], 0, 105, 0, 10, 10, '', 'L');

        // list headers
        $pdf = $this->CreateTextBox($pdf, 'Quantity', 0, 120, 20, 10, 10, 'B', 'C');
        $pdf = $this->CreateTextBox($pdf, 'Description', 20, 120, 90, 10, 10, 'B');
        $pdf = $this->CreateTextBox($pdf, 'Unit Price', 110, 120, 30, 10, 10, 'B', 'R');
        $pdf = $this->CreateTextBox($pdf, 'Total', 140, 120, 30, 10, 10, 'B', 'R');

        $pdf->Line(20, 129, 195, 129);

        // list items
        $currY = 128;
        $total = 0;
        foreach ($invoiceData['invoiceItems'] as $item) {
            $pdf = $this->CreateTextBox($pdf, $item->getQuantity(), 0, $currY, 20, 10, 10, '', 'C');
            $pdf = $this->CreateTextBox($pdf, $item->getName(), 20, $currY, 90, 10, 10, '');
            $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getCurrency().' '.$item->getUnitPrice(), 110, $currY, 30, 10, 10, '', 'R');
            $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getCurrency().' '.$item->getTotalPrice(), 140, $currY, 30, 10, 10, '', 'R');
            $currY = $currY+5;
        }
        $pdf->Line(20, $currY+4, 195, $currY+4);

        // output the total row
        $pdf = $this->CreateTextBox($pdf, 'SUBTOTAL:', 10, $currY+5, 135, 10, 10, '', 'R');
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getCurrency().' '.number_format($invoiceData['totalPrice_nett'], 2, '.', ''), 140, $currY+5, 30, 10, 10, '', 'R');
        $pdf = $this->CreateTextBox($pdf, 'TAX ('.$invoiceData['issuerData']['vat_rate'].' %):', 10, $currY+10, 135, 10, 10, '', 'R');
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getCurrency().' '.number_format($invoiceData['totalTaxes'], 2, '.', ''), 140, $currY+10, 30, 10, 10, '', 'R');
        $pdf = $this->CreateTextBox($pdf, 'TOTAL:', 10, $currY+15, 135, 10, 10, 'B', 'R');
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getCurrency().' '.number_format($invoiceData['outstandingAmount'], 2, '.', ''), 140, $currY+15, 30, 10, 10, 'B', 'R');

        // some payment instructions or information

        $invoiceUrl = $this->utilsServices->getUrlForRouteName($request, 'singleInvoice', $params = array('invoiceId' => $invoiceData['invoice']->getId()));

        $pdf->SetXY(20, $currY+30);
        $pdf->SetFont(PDF_FONT_NAME_MAIN, '', 10);
        $pdf->MultiCell(175, 10, '<b>Wire Transfer:</b><br> 
                                  International Society of Engineering Pedagogy<br>
                                  Bank Austria<br>
                                  BIC: BKAUATWW<br>
                                  Account Number: 50575084471<br>
                                  IBAN: AT021200050575084471<br>
                                  Reason for transfer: Invoice '.$invoiceData['invoiceId'].'<br><br>
                                  <b>With Credit Card via Paypal:</b><br>
                                  <a href="'.$invoiceUrl.'">Pay Now</a><br>', 0, 'L', 0, 1, '', '', true, null, true);

        // create content for signature (image and/or text)
        $pdf->Image('assets/images/pdf_invoice/stamp_s.jpg', 140, $currY+30, 37, 35, 'JPG');

        $pdf->SetY(266);
        $pdf->SetFont(PDF_FONT_NAME_MAIN, 'I', 8);
        $pdf->Cell(0, 10,  $invoiceData['issuerData']['nameOfOrganization'], 0, false, 'C');

        return array('exception' => false,
            'pdfInvoice' => $pdf->Output('invoice_'.$invoiceData['invoice']->getId().'.pdf', 'I'));

    }

    public function generatePdfReceipt($invoiceId, $userId, $request)
    {

        $invoiceData = $this->userServices->getInvoiceDataForUser($invoiceId, $userId);

        if ($invoiceData['exception']){
            return $invoiceData;
        }
        if ($invoiceData['outstandingAmount'] > 0){
            return array('exception' => true,
                'message' => 'Cannot generate receipt for Invoice #'.$invoiceId.'. Invoice has not been paid yet');
        }

        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document (meta) information
        $pdf->SetCreator($invoiceData['issuerData']['nameOfOrganization']);
        $pdf->SetAuthor($invoiceData['issuerData']['nameOfOrganization']);
        $pdf->SetTitle('Invoice Nr. '.$invoiceData['invoice']->getId());
        $pdf->SetSubject('Invoice Nr. '.$invoiceData['invoice']->getId());

// add a page
        $pdf->AddPage();

        $pdf->setJPEGQuality(90);
        $pdf->Image('assets/images/pdf_invoice/header.jpg', 20, 15, 170, 0, 'JPG', '');

// create address box of invoice issuer
        $pdf = $this->CreateTextBox($pdf, $invoiceData['issuerData']['nameOfOrganization'], 0, 40, 120, 10, 10, 'B');
        $pdf = $this->CreateTextBox($pdf,  $invoiceData['issuerData']['street'], 0, 45, 80, 10, 10);
        $pdf = $this->CreateTextBox($pdf,  $invoiceData['issuerData']['zip'].' '. $invoiceData['issuerData']['city'].' - '. $invoiceData['issuerData']['country'], 0, 50, 80, 10, 10);
        $pdf = $this->CreateTextBox($pdf,  $invoiceData['issuerData']['vat'].' | '. $invoiceData['issuerData']['registrationNumber'], 0, 55, 80, 10, 10);

// create address box
        $pdf = $this->CreateTextBox($pdf, 'BILLING ADDRESS::', 0, 65, 80, 10, 10, 'B');
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getBillingName(), 0, 70, 80, 10, 10);
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getBillingInstitution(), 0, 75, 80, 10, 10);
//$pdf->CreateTextBox($member['address1'], 0, 65, 80, 10, 10);
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getBillingStreet().' - '.$invoiceData['invoice']->getBillingzip().' '.$invoiceData['invoice']->getBillingCity(), 0, 80, 80, 10, 10);
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getBillingCountry(), 0, 85, 80, 10, 10);
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getBillingVat(), 0, 90, 80, 10, 10);
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getBillingReference(), 0, 95, 80, 10, 10);

        // date
        $pdf = $this->CreateTextBox($pdf, 'Date Issued: '.$invoiceData['invoiceDate'], 0, 100, 80, 10, 10);
        $pdf = $this->CreateTextBox($pdf, 'Date due: '.$invoiceData['invoiceDueDate'], 0, 105, 80, 10, 10);
// invoice title / number
        $pdf = $this->CreateTextBox($pdf, 'RECEIPT for Invoice #'.$invoiceData['invoiceId'], 0, 110, 120, 20, 16);


        // list headers
        $pdf = $this->CreateTextBox($pdf, 'Quantity', 0, 130, 20, 10, 10, 'B', 'C');
        $pdf = $this->CreateTextBox($pdf, 'Description', 20, 130, 90, 10, 10, 'B');
        $pdf = $this->CreateTextBox($pdf, 'Unit Price', 110, 130, 30, 10, 10, 'B', 'R');
        $pdf = $this->CreateTextBox($pdf, 'Total', 140, 130, 30, 10, 10, 'B', 'R');

        $pdf->Line(20, 139, 195, 139);

        // list items
        $currY = 138;
        $total = 0;
        foreach ($invoiceData['invoiceItems'] as $item) {
            $pdf = $this->CreateTextBox($pdf, $item->getQuantity(), 0, $currY, 20, 10, 10, '', 'C');
            $pdf = $this->CreateTextBox($pdf, $item->getName(), 20, $currY, 90, 10, 10, '');
            $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getCurrency().' '.$item->getUnitPrice(), 110, $currY, 30, 10, 10, '', 'R');
            $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getCurrency().' '.$item->getTotalPrice(), 140, $currY, 30, 10, 10, '', 'R');
            $currY = $currY+5;
        }
        $pdf->Line(20, $currY+4, 195, $currY+4);

        // output the total row
        $pdf = $this->CreateTextBox($pdf, 'SUBTOTAL:', 10, $currY+5, 135, 10, 10, '', 'R');
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getCurrency().' '.number_format($invoiceData['totalPrice_nett'], 2, '.', ''), 140, $currY+5, 30, 10, 10, '', 'R');
        $pdf = $this->CreateTextBox($pdf, 'TAX ('.$invoiceData['issuerData']['vat_rate'].' %):', 10, $currY+10, 135, 10, 10, '', 'R');
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getCurrency().' '.number_format($invoiceData['totalTaxes'], 2, '.', ''), 140, $currY+10, 30, 10, 10, '', 'R');
        $pdf = $this->CreateTextBox($pdf, 'TOTAL:', 10, $currY+15, 135, 10, 10, 'B', 'R');
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getCurrency().' '.number_format($invoiceData['outstandingAmount'], 2, '.', ''), 140, $currY+15, 30, 10, 10, 'B', 'R');

        // some payment instructions or information

        $invoiceUrl = $this->utilsServices->getUrlForRouteName($request, 'singleInvoice', $params = array('invoiceId' => $invoiceData['invoice']->getId()));

        $pdf->SetXY(20, $currY+30);
        $pdf->SetFont(PDF_FONT_NAME_MAIN, '', 10);
        $pdf->MultiCell(175, 10, '<b>Payment received on '.$invoiceData['paidDate'].'</b>', 0, 'L', 0, 1, '', '', true, null, true);

        // create content for signature (image and/or text)
        $pdf->Image('assets/images/pdf_invoice/stamp_s.jpg', 140, $currY+30, 37, 35, 'JPG');
        $pdf->Image('assets/images/pdf_invoice/paid.jpg', 40, $currY+40, 20, 12, 'JPG');

        $pdf->SetY(266);
        $pdf->SetFont(PDF_FONT_NAME_MAIN, 'I', 8);
        $pdf->Cell(0, 10,  $invoiceData['issuerData']['nameOfOrganization'], 0, false, 'C');

        return array('exception' => false,
            'pdfInvoice' => $pdf->Output('invoice_'.$invoiceData['invoice']->getId().'.pdf', 'I'));

    }

    public function CreateTextBox(TCPDF $pdf, $textval, $x = 0, $y, $width = 0, $height = 10, $fontsize = 10, $fontstyle = '', $align = 'L') {
        $pdf->SetXY($x+20, $y); // 20 = margin left
        $pdf->SetFont(PDF_FONT_NAME_MAIN, $fontstyle, $fontsize);
        $pdf->Cell($width, $height, $textval, 0, false, $align);

        return $pdf;
    }

}