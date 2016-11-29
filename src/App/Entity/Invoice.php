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

    /** @Column(type="boolean", nullable=true) * */
    protected $paid;

    /** @Column(type="string", length=500, nullable=true) * */
    protected $onPaymentActions;



    // ----- Billing Address

    /** @Column(type="string", length=50, nullable=true) **/
    protected $name;

    /** @Column(type="string", length=50, nullable=true) **/
    protected $institution;

    /** @Column(type="string", length=50, nullable=true) **/
    protected $street;

    /** @Column(type="string", length=50, nullable=true) **/
    protected $city;

    /** @Column(type="string", length=20, nullable=true) **/
    protected $zip;

    /** @Column(type="string", length=50, nullable=true) **/
    protected $country;

    /** @Column(type="string", length=50, nullable=true) **/
    protected $vat;

    /** @Column(type="string", length=50, nullable=true) **/
    protected $reference;
    


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
     * Set billingId
     *
     * @param integer $billingId
     *
     * @return Invoice
     */
    public function setBillingId($billingId)
    {
        $this->billingId = $billingId;

        return $this;
    }

    /**
     * Get billingId
     *
     * @return integer
     */
    public function getBillingId()
    {
        return $this->billingId;
    }

    /**
     * Set createDate
     *
     * @param string $createDate
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
     * @return string
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * Set dueDate
     *
     * @param string $dueDate
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
     * @return string
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Invoice
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set institution
     *
     * @param string $institution
     *
     * @return Invoice
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;

        return $this;
    }

    /**
     * Get institution
     *
     * @return string
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * Set street
     *
     * @param string $street
     *
     * @return Invoice
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return Invoice
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set zip
     *
     * @param string $zip
     *
     * @return Invoice
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get zip
     *
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return Invoice
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set vat
     *
     * @param string $vat
     *
     * @return Invoice
     */
    public function setVat($vat)
    {
        $this->vat = $vat;

        return $this;
    }

    /**
     * Get vat
     *
     * @return string
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * Set reference
     *
     * @param string $reference
     *
     * @return Invoice
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get reference
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
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
}
