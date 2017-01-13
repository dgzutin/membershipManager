<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 08.10.16
 * Time: 18:33
 */

// php vendor/bin/doctrine orm:generate:entities src/
// php vendor/bin/doctrine orm:schema-tool:update --force

namespace App\Entity;
/**
 * @Entity @Table(name="invoicePayment")
 **/

class InvoicePayment
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    protected $id;

    /** @Column(type="integer", nullable=false) * */
    protected $invoiceId;

    /** @Column(type="datetime", nullable=false) * */
    protected $datePaid;

    /** @Column(type="decimal", precision=7, scale=2) * */
    protected $amountPaid;

    /** @Column(type="string", length=50, nullable=true) * */
    protected $paymentNote;

    /** @Column(type="string", length=20, nullable=false) * */
    protected $paymentMode; //PAYPAL and WIRE_TRANSFER are supported

    /** @Column(type="string", length=100, nullable=true) * */
    protected $paypalTransactionId;

    /** @Column(type="string", length=100, nullable=true) * */
    protected $paypalPayerId;

    /** @Column(type="string", length=50, nullable=true) * */
    protected $paypalReceiver_email;

    /** @Column(type="string", length=50, nullable=true) * */
    protected $paypalIpnTrackId;



    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set invoiceId
     *
     * @param integer $invoiceId
     *
     * @return InvoicePayment
     */
    public function setInvoiceId($invoiceId)
    {
        $this->invoiceId = $invoiceId;

        return $this;
    }

    /**
     * Get invoiceId
     *
     * @return integer
     */
    public function getInvoiceId()
    {
        return $this->invoiceId;
    }

    /**
     * Set datePaid
     *
     * @param string $datePaid
     *
     * @return InvoicePayment
     */
    public function setDatePaid($datePaid)
    {
        $this->datePaid = $datePaid;

        return $this;
    }

    /**
     * Get datePaid
     *
     * @return string
     */
    public function getDatePaid()
    {
        return $this->datePaid;
    }

    /**
     * Set paymentNote
     *
     * @param string $paymentNote
     *
     * @return InvoicePayment
     */
    public function setPaymentNote($paymentNote)
    {
        $this->paymentNote = $paymentNote;

        return $this;
    }

    /**
     * Get paymentNote
     *
     * @return string
     */
    public function getPaymentNote()
    {
        return $this->paymentNote;
    }

    /**
     * Set amountPaid
     *
     * @param string $amountPaid
     *
     * @return InvoicePayment
     */
    public function setAmountPaid($amountPaid)
    {
        $this->amountPaid = $amountPaid;

        return $this;
    }

    /**
     * Get amountPaid
     *
     * @return string
     */
    public function getAmountPaid()
    {
        return $this->amountPaid;
    }

    /**
     * Set paymentMode
     *
     * @param string $paymentMode
     *
     * @return InvoicePayment
     */
    public function setPaymentMode($paymentMode)
    {
        $this->paymentMode = $paymentMode;

        return $this;
    }

    /**
     * Get paymentMode
     *
     * @return string
     */
    public function getPaymentMode()
    {
        return $this->paymentMode;
    }

    /**
     * Set paypalTransactionId
     *
     * @param integer $paypalTransactionId
     *
     * @return InvoicePayment
     */
    public function setPaypalTransactionId($paypalTransactionId)
    {
        $this->paypalTransactionId = $paypalTransactionId;

        return $this;
    }

    /**
     * Get paypalTransactionId
     *
     * @return integer
     */
    public function getPaypalTransactionId()
    {
        return $this->paypalTransactionId;
    }

    /**
     * Set paypalPayerId
     *
     * @param string $paypalPayerId
     *
     * @return InvoicePayment
     */
    public function setPaypalPayerId($paypalPayerId)
    {
        $this->paypalPayerId = $paypalPayerId;

        return $this;
    }

    /**
     * Get paypalPayerId
     *
     * @return string
     */
    public function getPaypalPayerId()
    {
        return $this->paypalPayerId;
    }

    /**
     * Set paypalReceiverEmail
     *
     * @param string $paypalReceiverEmail
     *
     * @return InvoicePayment
     */
    public function setPaypalReceiverEmail($paypalReceiverEmail)
    {
        $this->paypalReceiver_email = $paypalReceiverEmail;

        return $this;
    }

    /**
     * Get paypalReceiverEmail
     *
     * @return string
     */
    public function getPaypalReceiverEmail()
    {
        return $this->paypalReceiver_email;
    }

    /**
     * Set paypalIpnTrackId
     *
     * @param string $paypalIpnTrackId
     *
     * @return InvoicePayment
     */
    public function setPaypalIpnTrackId($paypalIpnTrackId)
    {
        $this->paypalIpnTrackId = $paypalIpnTrackId;

        return $this;
    }

    /**
     * Get paypalIpdTrackId
     *
     * @return string
     */
    public function getPaypalIpnTrackId()
    {
        return $this->paypalIpnTrackId;
    }
}
