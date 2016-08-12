<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 09.08.16
 * Time: 16:39
 */
// php vendor/bin/doctrine orm:generate:entities src/
// php vendor/bin/doctrine orm:schema-tool:update --force



namespace App\Entity;
/**
 * @Entity @Table(name="invoiceItem")
 **/

class InvoiceItem
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    protected $id;

    /** @Column(type="integer", nullable=false) * */
    protected $invoiceId;

    /** @Column(type="integer", nullable=false) * */
    protected $billingId;

    /** @Column(type="string", length=100, nullable=false) * */
    protected $name;

    /** @Column(type="string", length=500, nullable=false) * */
    protected $description;

    /** @Column(type="integer", nullable=false) * */
    protected $quantity;

    /** @Column(type="decimal", precision=7, scale=2) * */
    protected $unitPrice;

    /** @Column(type="decimal", precision=7, scale=2) * */
    protected $totalPrice;


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
     * @return InvoiceItem
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
     * Set billingId
     *
     * @param integer $billingId
     *
     * @return InvoiceItem
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
     * Set name
     *
     * @param string $name
     *
     * @return InvoiceItem
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
     * Set description
     *
     * @param string $description
     *
     * @return InvoiceItem
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return InvoiceItem
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return integer
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set price
     *
     * @param string $price
     *
     * @return InvoiceItem
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set unitPrice
     *
     * @param string $unitPrice
     *
     * @return InvoiceItem
     */
    public function setUnitPrice($unitPrice)
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

    /**
     * Get unitPrice
     *
     * @return string
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * Set totalPrice
     *
     * @param string $totalPrice
     *
     * @return InvoiceItem
     */
    public function setTotalPrice($totalPrice)
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    /**
     * Get totalPrice
     *
     * @return string
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }
}
