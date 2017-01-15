<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 09.08.16
 * Time: 16:38
 */

namespace App\Entity;
/**
 * @Entity @Table(name="invoice")
 **/

class Invoice
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    protected $id;

    /** @Column(type="integer", nullable=false) * */
    protected $userId;

    /** @Column(type="datetime", nullable=false) * */
    protected $createDate;

    /** @Column(type="datetime", nullable=false) * */
    protected $dueDate;

    /** @Column(type="string", length=10, nullable=false) * */
    protected $currency;

    /** @Column(type="string", length=500, nullable=true) * */
    protected $invoiceNote;

    /** @Column(type="datetime", nullable=true) * */
    protected $paidDate;

    /** @Column(type="string", length=100, nullable=true) **/
    protected $inv_organization;

    /** @Column(type="string", length=50, nullable=true) **/
    protected $inv_street;

    /** @Column(type="string", length=50, nullable=true) **/
    protected $inv_city;

    /** @Column(type="string", length=20, nullable=true) **/
    protected $inv_zip;

    /** @Column(type="string", length=50, nullable=true) **/
    protected $inv_country;

    /** @Column(type="string", length=75, nullable=true) **/
    protected $inv_email;

    /** @Column(type="string", length=50, nullable=true) **/
    protected $inv_vat;

    /** @Column(type="string", length=50, nullable=true) **/
    protected $inv_phone;

    /** @Column(type="string", length=50, nullable=true) **/
    protected $inv_registrationNumber;

    /** @Column(type="decimal", nullable=true) **/
    protected $vat_rate;

    /** @Column(type="boolean", nullable=true) * */
    protected $paid;

    /** @Column(type="string", length=500, nullable=true) * */
    protected $onPaymentActions;

    /** @Column(type="boolean", nullable=true) * */
    protected $actionsExecuted;



    // ----- Billing Address

    /** @Column(type="string", length=50, nullable=true) **/
    protected $billing_name;

    /** @Column(type="string", length=50, nullable=true) **/
    protected $billing_institution;

    /** @Column(type="string", length=50, nullable=true) **/
    protected $billing_street;

    /** @Column(type="string", length=50, nullable=true) **/
    protected $billing_city;

    /** @Column(type="string", length=20, nullable=true) **/
    protected $billing_zip;

    /** @Column(type="string", length=50, nullable=true) **/
    protected $billing_country;

    /** @Column(type="string", length=50, nullable=true) **/
    protected $billing_vat;

    /** @Column(type="string", length=50, nullable=true) **/
    protected $billing_reference;
    

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
     * Set userId
     *
     * @param integer $userId
     *
     * @return Invoice
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set createDate
     *
     * @param \DateTime $createDate
     *
     * @return Invoice
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;

        return $this;
    }

    /**
     * Get createDate
     *
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * Set dueDate
     *
     * @param \DateTime $dueDate
     *
     * @return Invoice
     */
    public function setDueDate($dueDate)
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    /**
     * Get dueDate
     *
     * @return \DateTime
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * Set currency
     *
     * @param string $currency
     *
     * @return Invoice
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set invoiceNote
     *
     * @param string $invoiceNote
     *
     * @return Invoice
     */
    public function setInvoiceNote($invoiceNote)
    {
        $this->invoiceNote = $invoiceNote;

        return $this;
    }

    /**
     * Get invoiceNote
     *
     * @return string
     */
    public function getInvoiceNote()
    {
        return $this->invoiceNote;
    }

    /**
     * Set paidDate
     *
     * @param \DateTime $paidDate
     *
     * @return Invoice
     */
    public function setPaidDate($paidDate)
    {
        $this->paidDate = $paidDate;

        return $this;
    }

    /**
     * Get paidDate
     *
     * @return \DateTime
     */
    public function getPaidDate()
    {
        return $this->paidDate;
    }

    /**
     * Set invOrganization
     *
     * @param string $invOrganization
     *
     * @return Invoice
     */
    public function setInvOrganization($invOrganization)
    {
        $this->inv_organization = $invOrganization;

        return $this;
    }

    /**
     * Get invOrganization
     *
     * @return string
     */
    public function getInvOrganization()
    {
        return $this->inv_organization;
    }

    /**
     * Set invStreet
     *
     * @param string $invStreet
     *
     * @return Invoice
     */
    public function setInvStreet($invStreet)
    {
        $this->inv_street = $invStreet;

        return $this;
    }

    /**
     * Get invStreet
     *
     * @return string
     */
    public function getInvStreet()
    {
        return $this->inv_street;
    }

    /**
     * Set invCity
     *
     * @param string $invCity
     *
     * @return Invoice
     */
    public function setInvCity($invCity)
    {
        $this->inv_city = $invCity;

        return $this;
    }

    /**
     * Get invCity
     *
     * @return string
     */
    public function getInvCity()
    {
        return $this->inv_city;
    }

    /**
     * Set invZip
     *
     * @param string $invZip
     *
     * @return Invoice
     */
    public function setInvZip($invZip)
    {
        $this->inv_zip = $invZip;

        return $this;
    }

    /**
     * Get invZip
     *
     * @return string
     */
    public function getInvZip()
    {
        return $this->inv_zip;
    }

    /**
     * Set invCountry
     *
     * @param string $invCountry
     *
     * @return Invoice
     */
    public function setInvCountry($invCountry)
    {
        $this->inv_country = $invCountry;

        return $this;
    }

    /**
     * Get invCountry
     *
     * @return string
     */
    public function getInvCountry()
    {
        return $this->inv_country;
    }

    /**
     * Set invVat
     *
     * @param string $invVat
     *
     * @return Invoice
     */
    public function setInvVat($invVat)
    {
        $this->inv_vat = $invVat;

        return $this;
    }

    /**
     * Get invVat
     *
     * @return string
     */
    public function getInvVat()
    {
        return $this->inv_vat;
    }

    /**
     * Set invRegistrationNumber
     *
     * @param string $invRegistrationNumber
     *
     * @return Invoice
     */
    public function setInvRegistrationNumber($invRegistrationNumber)
    {
        $this->inv_registrationNumber = $invRegistrationNumber;

        return $this;
    }

    /**
     * Get invRegistrationNumber
     *
     * @return string
     */
    public function getInvRegistrationNumber()
    {
        return $this->inv_registrationNumber;
    }

    /**
     * Set vatRate
     *
     * @param string $vatRate
     *
     * @return Invoice
     */
    public function setVatRate($vatRate)
    {
        $this->vat_rate = $vatRate;

        return $this;
    }

    /**
     * Get vatRate
     *
     * @return string
     */
    public function getVatRate()
    {
        return $this->vat_rate;
    }

    /**
     * Set paid
     *
     * @param boolean $paid
     *
     * @return Invoice
     */
    public function setPaid($paid)
    {
        $this->paid = $paid;

        return $this;
    }

    /**
     * Get paid
     *
     * @return boolean
     */
    public function getPaid()
    {
        return $this->paid;
    }

    /**
     * Set onPaymentActions
     *
     * @param string $onPaymentActions
     *
     * @return Invoice
     */
    public function setOnPaymentActions($onPaymentActions)
    {
        $this->onPaymentActions = $onPaymentActions;

        return $this;
    }

    /**
     * Get onPaymentActions
     *
     * @return string
     */
    public function getOnPaymentActions()
    {
        return $this->onPaymentActions;
    }

    /**
     * Set actionsExecuted
     *
     * @param boolean $actionsExecuted
     *
     * @return Invoice
     */
    public function setActionsExecuted($actionsExecuted)
    {
        $this->actionsExecuted = $actionsExecuted;

        return $this;
    }

    /**
     * Get actionsExecuted
     *
     * @return boolean
     */
    public function getActionsExecuted()
    {
        return $this->actionsExecuted;
    }

    /**
     * Set billingName
     *
     * @param string $billingName
     *
     * @return Invoice
     */
    public function setBillingName($billingName)
    {
        $this->billing_name = $billingName;

        return $this;
    }

    /**
     * Get billingName
     *
     * @return string
     */
    public function getBillingName()
    {
        return $this->billing_name;
    }

    /**
     * Set billingInstitution
     *
     * @param string $billingInstitution
     *
     * @return Invoice
     */
    public function setBillingInstitution($billingInstitution)
    {
        $this->billing_institution = $billingInstitution;

        return $this;
    }

    /**
     * Get billingInstitution
     *
     * @return string
     */
    public function getBillingInstitution()
    {
        return $this->billing_institution;
    }

    /**
     * Set billingStreet
     *
     * @param string $billingStreet
     *
     * @return Invoice
     */
    public function setBillingStreet($billingStreet)
    {
        $this->billing_street = $billingStreet;

        return $this;
    }

    /**
     * Get billingStreet
     *
     * @return string
     */
    public function getBillingStreet()
    {
        return $this->billing_street;
    }

    /**
     * Set billingCity
     *
     * @param string $billingCity
     *
     * @return Invoice
     */
    public function setBillingCity($billingCity)
    {
        $this->billing_city = $billingCity;

        return $this;
    }

    /**
     * Get billingCity
     *
     * @return string
     */
    public function getBillingCity()
    {
        return $this->billing_city;
    }

    /**
     * Set billingZip
     *
     * @param string $billingZip
     *
     * @return Invoice
     */
    public function setBillingZip($billingZip)
    {
        $this->billing_zip = $billingZip;

        return $this;
    }

    /**
     * Get billingZip
     *
     * @return string
     */
    public function getBillingZip()
    {
        return $this->billing_zip;
    }

    /**
     * Set billingCountry
     *
     * @param string $billingCountry
     *
     * @return Invoice
     */
    public function setBillingCountry($billingCountry)
    {
        $this->billing_country = $billingCountry;

        return $this;
    }

    /**
     * Get billingCountry
     *
     * @return string
     */
    public function getBillingCountry()
    {
        return $this->billing_country;
    }

    /**
     * Set billingVat
     *
     * @param string $billingVat
     *
     * @return Invoice
     */
    public function setBillingVat($billingVat)
    {
        $this->billing_vat = $billingVat;

        return $this;
    }

    /**
     * Get billingVat
     *
     * @return string
     */
    public function getBillingVat()
    {
        return $this->billing_vat;
    }

    /**
     * Set billingReference
     *
     * @param string $billingReference
     *
     * @return Invoice
     */
    public function setBillingReference($billingReference)
    {
        $this->billing_reference = $billingReference;

        return $this;
    }

    /**
     * Get billingReference
     *
     * @return string
     */
    public function getBillingReference()
    {
        return $this->billing_reference;
    }

    /**
     * Set invEmail
     *
     * @param string $invEmail
     *
     * @return Invoice
     */
    public function setInvEmail($invEmail)
    {
        $this->inv_email = $invEmail;

        return $this;
    }

    /**
     * Get invEmail
     *
     * @return string
     */
    public function getInvEmail()
    {
        return $this->inv_email;
    }

    /**
     * Set iban
     *
     * @param string $iban
     *
     * @return Invoice
     */
    public function setIban($iban)
    {
        $this->iban = $iban;

        return $this;
    }

    /**
     * Get iban
     *
     * @return string
     */
    public function getIban()
    {
        return $this->iban;
    }

    /**
     * Set bic
     *
     * @param string $bic
     *
     * @return Invoice
     */
    public function setBic($bic)
    {
        $this->bic = $bic;

        return $this;
    }

    /**
     * Get bic
     *
     * @return string
     */
    public function getBic()
    {
        return $this->bic;
    }

    /**
     * Set bankName
     *
     * @param string $bankName
     *
     * @return Invoice
     */
    public function setBankName($bankName)
    {
        $this->bankName = $bankName;

        return $this;
    }

    /**
     * Get bankName
     *
     * @return string
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * Set bankAddress
     *
     * @param string $bankAddress
     *
     * @return Invoice
     */
    public function setBankAddress($bankAddress)
    {
        $this->bankAddress = $bankAddress;

        return $this;
    }

    /**
     * Get bankAddress
     *
     * @return string
     */
    public function getBankAddress()
    {
        return $this->bankAddress;
    }

    /**
     * Set invPhone
     *
     * @param string $invPhone
     *
     * @return Invoice
     */
    public function setInvPhone($invPhone)
    {
        $this->inv_phone = $invPhone;

        return $this;
    }

    /**
     * Get invPhone
     *
     * @return string
     */
    public function getInvPhone()
    {
        return $this->inv_phone;
    }
}
