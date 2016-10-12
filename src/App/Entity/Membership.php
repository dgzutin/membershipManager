<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 21.05.16
 * Time: 17:34
 */
namespace App\Entity;
/**
 * @Entity @Table(name="membership")
 **/

class Membership
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    protected $id;

    /** @Column(type="integer",  nullable=false) **/
    protected $memberId;

    /** @Column(type="integer",  nullable=false) **/
    protected $billingAddressId;

    /** @Column(type="integer", nullable=false) **/
    protected $membershipType;

    /** @Column(type="string", length=50, nullable=false) * */
    protected $memberRegDate;

    /** @Column(type="string", length=100, nullable=true) * */
    protected $quickRenewKey;

    /** @Column(type="boolean", nullable=false) * */
    protected $active;


    

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
     * Set memberId
     *
     * @param integer $memberId
     *
     * @return Membership
     */
    public function setMemberId($memberId)
    {
        $this->memberId = $memberId;

        return $this;
    }

    /**
     * Get memberId
     *
     * @return integer
     */
    public function getMemberId()
    {
        return $this->memberId;
    }

    /**
     * Set membershipType
     *
     * @param integer $membershipType
     *
     * @return Membership
     */
    public function setMembershipType($membershipType)
    {
        $this->membershipType = $membershipType;

        return $this;
    }

    /**
     * Get membershipType
     *
     * @return integer
     */
    public function getMembershipType()
    {
        return $this->membershipType;
    }

    /**
     * Set memberRegDate
     *
     * @param string $memberRegDate
     *
     * @return Membership
     */
    public function setMemberRegDate($memberRegDate)
    {
        $this->memberRegDate = $memberRegDate;

        return $this;
    }

    /**
     * Get memberRegDate
     *
     * @return string
     */
    public function getMemberRegDate()
    {
        return $this->memberRegDate;
    }

    /**
     * Set quickRenewKey
     *
     * @param string $quickRenewKey
     *
     * @return Membership
     */
    public function setQuickRenewKey($quickRenewKey)
    {
        $this->quickRenewKey = $quickRenewKey;

        return $this;
    }

    /**
     * Get quickRenewKey
     *
     * @return string
     */
    public function getQuickRenewKey()
    {
        return $this->quickRenewKey;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return Membership
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set billingAddressId
     *
     * @param integer $billingAddressId
     *
     * @return Membership
     */
    public function setBillingAddressId($billingAddressId)
    {
        $this->billingAddressId = $billingAddressId;

        return $this;
    }

    /**
     * Get billingAddressId
     *
     * @return integer
     */
    public function getBillingAddressId()
    {
        return $this->billingAddressId;
    }
}
