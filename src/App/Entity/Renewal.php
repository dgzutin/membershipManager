<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 24.05.16
 * Time: 11:20
 */

namespace App\Entity;
/**
 * @Entity @Table(name="renewal")
 **/

class Renewal
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    protected $id;

    /** @Column(type="integer", length=50, nullable=false) * */
    protected $memberId;

    /** @Column(type="string", length=50, nullable=false) * */
    protected $membershipType;

    /** @Column(type="string", length=10, nullable=false) * */
    protected $renewedForYear;

    /** @Column(type="string", length=50, nullable=false) * */
    protected $renewalDate;


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
     * Set renewedForYear
     *
     * @param string $renewedForYear
     *
     * @return Renewal
     */
    public function setRenewedForYear($renewedForYear)
    {
        $this->renewed_for_year = $renewedForYear;

        return $this;
    }

    /**
     * Get renewedForYear
     *
     * @return string
     */
    public function getRenewedForYear()
    {
        return $this->renewed_for_year;
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
}
