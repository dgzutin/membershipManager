<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 25.10.16
 * Time: 16:30
 */
namespace App\Service;

use TCPDF;

//require_once('tcpdf_include.php');

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

        //get System Info

        $systemInfo = $this->userServices->getSystemInfo();

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

        // set style for barcode
        $style = array(
            'border' => 1,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false, //array(255,255,255)
            'module_width' => 1, // width of a single module in points
            'module_height' => 1 // height of a single module in points
        );

        $invoiceUrl = $this->utilsServices->getUrlForRouteName($request, 'singleInvoice', $params = array('invoiceId' => $invoiceData['invoice']->getId()));

        // QRCODE,H : QR-CODE Best error correction
        $pdf->write2DBarcode($invoiceUrl, 'QRCODE,H', 155, 40, 35, 35, $style, 'N');
        //$pdf->write2DBarcode($invoiceUrl, 'PDF417,3,4', 140, 40, 80, 80, $style, 'N');

// create address box of invoice issuer
        $pdf = $this->CreateTextBox($pdf, $invoiceData['issuerData']['nameOfOrganization'], 0, 40, 120, 10, 10, 'B');
        $pdf = $this->CreateTextBox($pdf,  $invoiceData['issuerData']['street'], 0, 45, 80, 10, 10);
        $pdf = $this->CreateTextBox($pdf,  $invoiceData['issuerData']['zip'].' '. $invoiceData['issuerData']['city'].' - '. $this->utilsServices->getCountryNameByCode($invoiceData['issuerData']['country']), 0, 50, 80, 10, 10);
        $pdf = $this->CreateTextBox($pdf,  $invoiceData['issuerData']['vat'].' | '. $invoiceData['issuerData']['registrationNumber'], 0, 55, 80, 10, 10);

// create address box
        $pdf = $this->CreateTextBox($pdf, 'BILLING ADDRESS:', 0, 65, 80, 10, 10, 'B');
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getBillingName(), 0, 70, 80, 10, 10);
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getBillingInstitution(), 0, 75, 80, 10, 10);
//$pdf->CreateTextBox($member['address1'], 0, 65, 80, 10, 10);
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getBillingStreet().' - '.$invoiceData['invoice']->getBillingzip().' '.$invoiceData['invoice']->getBillingCity().', '.$this->utilsServices->getCountryNameByCode($invoiceData['invoice']->getBillingCountry()), 0, 80, 80, 10, 10);
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getBillingVat(), 0, 85, 90, 10, 10);
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getBillingReference(), 0, 90, 80, 10, 10);

        // date
        $pdf = $this->CreateTextBox($pdf, 'Date Issued: '.$invoiceData['invoiceDate'], 95, 75, 80, 10, 10,'','R');
        $pdf = $this->CreateTextBox($pdf, 'Date due: '.$invoiceData['invoiceDueDate'], 95, 80, 80, 10, 10,'','R');
// invoice title / number
        $pdf = $this->CreateTextBox($pdf, 'Invoice #'.$invoiceData['invoiceId'], 0, 95, 120, 20, 16);

        $pdf->SetFont(PDF_FONT_NAME_MAIN, '', 10);
        $currY = 110;
        $i = 0;
        if ($invoiceData['invoice']->getInvoiceText() != null){

            $pdf->SetXY(20, $currY+$i);
            $pdf->MultiCell(155, 10, $invoiceData['invoice']->getInvoiceText(), 0, 'L', 0, 1, '', '', true, null, true);
            $i = $i + 15;
        }
        $currY = $currY + $i;

        // list headers
        $pdf = $this->CreateTextBox($pdf, 'Quantity', 0, $currY, 20, 10, 10, 'B', 'C');
        $pdf = $this->CreateTextBox($pdf, 'Description', 20, $currY, 90, 10, 10, 'B');
        $pdf = $this->CreateTextBox($pdf, 'Unit Price', 110, $currY, 30, 10, 10, 'B', 'R');
        $pdf = $this->CreateTextBox($pdf, 'Total', 140, $currY, 30, 10, 10, 'B', 'R');

        $pdf->Line(20, $currY+9, 195, $currY+9);

        $currY = $currY +9;

        // list items

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
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getCurrency().' '.number_format($invoiceData['totalPrice'], 2, '.', ''), 140, $currY+15, 30, 10, 10, 'B', 'R');

        // some payment instructions or information

        $pdf->SetXY(20, $currY+25);
        $pdf->SetFont(PDF_FONT_NAME_MAIN, '', 10);

        $pdf->MultiCell(165, 10, '<b>Possible payment methods are:</b><br> <br>', 0, 'L', 0, 1, '', '', true, null, true);

        $i = 30;
        if ($systemInfo['settings']->getWireTransferActive()){

            $pdf->SetXY(20, $currY+$i);
            $pdf->MultiCell(155, 10, '<b>Wire Transfer:</b><br> 
                                  '.$systemInfo['settings']->getNameOfOrganization().'<br>
                                  '.$systemInfo['settings']->getBankName().'<br>
                                  BIC: '.$systemInfo['settings']->getBic().'<br>
                                  IBAN: '.$systemInfo['settings']->getIban().'<br>
                                  Reason for transfer: Invoice '.$invoiceData['invoice']->getId().'<br><br>', 0, 'L', 0, 1, '', '', true, null, true);
            $i = $i + 30;
        }

        if ($systemInfo['settings']->getPaypalActive()){
            $pdf->SetXY(20, $currY+$i);
            $pdf->MultiCell(165, 40, '<b>Credit Card via Paypal:</b><br>
                                  <a href="'.$invoiceUrl.'"><img width="120px" src="assets/images/pay_now_button.jpg"></a><br>', 0, 'L', 0, 1, '', '', true, null, true);
        }

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

        // set style for barcode
        $style = array(
            'border' => 1,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false, //array(255,255,255)
            'module_width' => 1, // width of a single module in points
            'module_height' => 1 // height of a single module in points
        );

        $invoiceUrl = $this->utilsServices->getUrlForRouteName($request, 'singleInvoice', $params = array('invoiceId' => $invoiceData['invoice']->getId()));

        // QRCODE,H : QR-CODE Best error correction
        $pdf->write2DBarcode($invoiceUrl, 'QRCODE,H', 155, 40, 35, 35, $style, 'N');
        //$pdf->write2DBarcode($invoiceUrl, 'PDF417,3,4', 140, 40, 80, 80, $style, 'N');

        // create address box of invoice issuer
        $pdf = $this->CreateTextBox($pdf, $invoiceData['issuerData']['nameOfOrganization'], 0, 40, 120, 10, 10, 'B');
        $pdf = $this->CreateTextBox($pdf,  $invoiceData['issuerData']['street'], 0, 45, 80, 10, 10);
        $pdf = $this->CreateTextBox($pdf,  $invoiceData['issuerData']['zip'].' '. $invoiceData['issuerData']['city'].' - '. $this->utilsServices->getCountryNameByCode($invoiceData['issuerData']['country']), 0, 50, 80, 10, 10);
        $pdf = $this->CreateTextBox($pdf,  $invoiceData['issuerData']['vat'].' | '. $invoiceData['issuerData']['registrationNumber'], 0, 55, 80, 10, 10);

        // create address box
        $pdf = $this->CreateTextBox($pdf, 'BILLING ADDRESS::', 0, 65, 80, 10, 10, 'B');
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getBillingName(), 0, 70, 80, 10, 10);
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getBillingInstitution(), 0, 75, 80, 10, 10);
//$pdf->CreateTextBox($member['address1'], 0, 65, 80, 10, 10);
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getBillingStreet().' - '.$invoiceData['invoice']->getBillingzip().', '.$this->utilsServices->getCountryNameByCode($invoiceData['invoice']->getBillingCountry()), 0, 80, 80, 10, 10);
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getBillingVat(), 0, 85, 80, 10, 10);
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getBillingReference(), 0, 90, 80, 10, 10);

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
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getCurrency().' '.number_format($invoiceData['totalPrice'], 2, '.', ''), 140, $currY+15, 30, 10, 10, 'B', 'R');
        $pdf = $this->CreateTextBox($pdf, 'TOTAL PAID:', 10, $currY+20, 135, 10, 10, 'B', 'R');
        $pdf = $this->CreateTextBox($pdf, $invoiceData['invoice']->getCurrency().' '.number_format($invoiceData['amountPaid'], 2, '.', ''), 140, $currY+20, 30, 10, 10, 'B', 'R');

        // some payment instructions or information

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

    public function generatePdfMemberCertificate($member, $userId)
    {
        //$invoiceData = $this->userServices->getInvoiceDataForUser($invoiceId, $userId);

        //$member = json_decode($memberStr);

        $systemInfo = $this->userServices->getSystemInfo();
        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document (meta) information
        $pdf->SetCreator($systemInfo['settings']->getnameOfOrganization());
        $pdf->SetAuthor($systemInfo['settings']->getnameOfOrganization());
        $pdf->SetTitle('Membership Certificate');
        $pdf->SetSubject('Certificate Member '.$member['member']['membership']->getMemberId());

        // add a page
        $pdf->AddPage();
        $pdf->setJPEGQuality(90);

        // get the current page break margin
        $bMargin = $pdf->getBreakMargin();
        // get current auto-page-break mode
        $auto_page_break = $pdf->getAutoPageBreak();
        // disable auto-page-break
        $pdf->SetAutoPageBreak(false, 0);
        // set bacground image
        $img_file = 'assets/images/cert_back.jpg';
        $pdf->Image($img_file, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
        // restore auto-page-break status
        $pdf->SetAutoPageBreak($auto_page_break, $bMargin);
        // set the starting point for the page content
        $pdf->setPageMark();

        $lines = array(
            array('yPos' => 100, 'text' => 'This certifies that', 'fontSize' => 20)

        );

        $pdf = $this->CreateTextBox($pdf, 'CERTIFICATE OF MEMBERSHIP', 28, 100, 120, 20, 20, 'B', 'L');
        $pdf = $this->CreateTextBox($pdf, 'This certifies that', 60, 120, 120, 20, 15);

        $nameAndTile = $member['member']['user']->getTitle().' '.$member['member']['user']->getFirstName().' '.$member['member']['user']->getLastName();
        $ratio = 170/48;
        $Xoffset = floor(((48-strlen($nameAndTile))/2)*$ratio);
        $pdf = $this->CreateTextBox($pdf, $nameAndTile, $Xoffset, 135, 120, 20, 20);

        $text = 'is a registered member of the';
        $ratio = 170/68;
        $Xoffset = floor(((68-strlen($text))/2)*$ratio);
        $pdf = $this->CreateTextBox($pdf, $text, $Xoffset, 150, 120, 20, 15);


        $text = $systemInfo['settings']->getnameOfOrganization();
        $ratio = 170/68;
        $Xoffset = floor(((68-strlen($text))/2)*$ratio);
        $pdf = $this->CreateTextBox($pdf, $text, $Xoffset, 160, 120, 20, 15);

        $text = 'Type: '.$member['member']['membershipTypeName'];
        $ratio = 170/68;
        $Xoffset = floor(((68-strlen($text))/2)*$ratio);
        $pdf = $this->CreateTextBox($pdf, $text, $Xoffset, 170, 120, 20, 12);

        //"Y-m-d H:i:s"
        $date = new \DateTime();

        $pdf = $this->CreateTextBox($pdf, 'Certificate issued on '.$date->format('jS F Y'), 0, 250, 120, 20, 10);
        if ($member['member']['valid'] && $member['member']['validity_string'] != 'n/a') {
            $pdf = $this->CreateTextBox($pdf, 'This '.$systemInfo['settings']->getAcronym().' membership is valid until ' . $member['member']['validity_string'], 0, 255, 120, 20, 10);
        }


        // list headers

        $pdf->SetFont(PDF_FONT_NAME_MAIN, 'B', 10);
      //  $pdf->MultiCell(175, 10, '<b>Payment received on '.$invoiceData['paidDate'].'</b>', 0, 'L', 0, 1, '', '', true, null, true);

        // create content for signature (image and/or text)
        $currY = 200;
        //->Image('assets/images/pdf_invoice/stamp_s.jpg', 140, $currY+30, 37, 35, 'JPG');
       // $pdf->Image('assets/images/pdf_invoice/paid.jpg', 40, $currY+40, 20, 12, 'JPG');



        return array('exception' => false,
            'pdfInvoice' => $pdf->Output('certificate'.$member['member']['membership']->getMemberId().'.pdf', 'I'));

    }

    public function CreateTextBox(TCPDF $pdf, $textval, $x = 0, $y, $width = 0, $height = 10, $fontsize = 10, $fontstyle = '', $align = 'L') {
        $pdf->SetXY($x+20, $y); // 20 = margin left
        $pdf->SetFont(PDF_FONT_NAME_MAIN, $fontstyle, $fontsize);
        $pdf->Cell($width, $height, $textval, 0, false, $align);

        return $pdf;
    }

}