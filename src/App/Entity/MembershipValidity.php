<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 24.05.16
 * Time: 11:20
 */

namespace App\Entity;
/**
 * @Entity @Table(name="MembershipValidity")
 **/

class MembershipValidity
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    protected $id;

    /** @Column(type="integer", nullable=false) * */
    protected $memberId;

    /** @Column(type="string", length=50, nullable=false) * */
    protected $membershipType;

    /** @Column(type="string", length=50, nullable=false) * */
    protected $validFrom;

    /** @Column(type="string", length=50, nullable=false) * */
    protected $validUntil;

    /** @Column(type="string", length=50, nullable=false) * */
    protected $date;


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
     * @return Renewal
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
     * @param string $membershipType
     *
     * @return Renewal
     */
    public function setMembershipType($membershipType)
    {
        $this->membershipType = $membershipType;

        return $this;
    }

    /**
     * Get membershipType
     *
     * @return string
     */
    public function getMembershipType()
    {
        return $this->membershipType;
    }


    /**
     * Set date
     *
     * @param string $date
     *
     * @return Renewal
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set renewalDate
     *
     * @param string $renewalDate
     *
     * @return Renewal
     */
    public function setRenewalDate($renewalDate)
    {
        $this->renewalDate = $renewalDate;

        return $this;
    }

    /**
     * Get renewalDate
     *
     * @return string
     */
    public function getRenewalDate()
    {
        return $this->renewalDate;
    }

    /**
     * Set renewedUntil
     *
     * @param string $renewedUntil
     *
     * @return Renewal
     */
    public function setRenewedUntil($renewedUntil)
    {
        $this->renewedUntil = $renewedUntil;

        return $this;
    }

    /**
     * Get renewedUntil
     *
     * @return string
     */
    public function getRenewedUntil()
    {
        return $this->renewedUntil;
    }

    /**
     * Set validFrom
     *
     * @param string $validFrom
     *
     * @return Renewal
     */
    public function setValidFrom($validFrom)
    {
        $this->validFrom = $validFrom;

        return $this;
    }

    /**
     * Get validFrom
     *
     * @return string
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }

    /**
     * Set validUntil
     *
     * @param string $validUntil
     *
     * @return Renewal
     */
    public function setValidUntil($validUntil)
    {
        $this->validUntil = $validUntil;

        return $this;
    }

    /**
     * Get validUntil
     *
     * @return string
     */
    public function getValidUntil()
    {
        return $this->validUntil;
    }
}
