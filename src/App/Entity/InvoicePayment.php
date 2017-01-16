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
    

    /** @Column(type="text",  nullable=true) * */
    protected $paymentGatewayData;

    /** @Column(type="text",  nullable=true) * */
    protected $systemMessage;
    

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
     * @param \DateTime $datePaid
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
     * @return \DateTime
     */
    public function getDatePaid()
    {
        return $this->datePaid;
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
     * Set paymentGatewayData
     *
     * @param string $paymentGatewayData
     *
     * @return InvoicePayment
     */
    public function setPaymentGatewayData($paymentGatewayData)
    {
        $this->paymentGatewayData = $paymentGatewayData;

        return $this;
    }

    /**
     * Get paymentGatewayData
     *
     * @return string
     */
    public function getPaymentGatewayData()
    {
        return $this->paymentGatewayData;
    }

    /**
     * Set systemMessage
     *
     * @param string $systemMessage
     *
     * @return InvoicePayment
     */
    public function setSystemMessage($systemMessage)
    {
        $this->systemMessage = $systemMessage;

        return $this;
    }

    /**
     * Get systemMessage
     *
     * @return string
     */
    public function getSystemMessage()
    {
        return $this->systemMessage;
    }
}
