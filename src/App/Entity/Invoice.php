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

    /** @Column(type="integer", length=50, nullable=false) * */
    protected $userId;

    /** @Column(type="integer", length=50, nullable=false) * */
    protected $billingId;

    /** @Column(type="string", length=50, nullable=false) * */
    protected $createDate;

    /** @Column(type="string", length=50, nullable=false) * */
    protected $dueDate;
    


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
}
