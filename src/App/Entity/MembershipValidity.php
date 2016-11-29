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
    protected $membershipId;
    

    /** @Column(type="datetime", nullable=false) * */
    protected $validFrom;

    /** @Column(type="datetime", nullable=false) * */
    protected $validUntil;

    /** @Column(type="datetime", nullable=false) * */
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
     * Set membershipId
     *
     * @param integer $membershipId
     *
     * @return MembershipValidity
     */
    public function setMembershipId($membershipId)
    {
        $this->membershipId = $membershipId;

        return $this;
    }

    /**
     * Get membershipId
     *
     * @return integer
     */
    public function getMembershipId()
    {
        return $this->membershipId;
    }

    /**
     * Set validFrom
     *
     * @param \DateTime $validFrom
     *
     * @return MembershipValidity
     */
    public function setValidFrom($validFrom)
    {
        $this->validFrom = $validFrom;

        return $this;
    }

    /**
     * Get validFrom
     *
     * @return \DateTime
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }

    /**
     * Set validUntil
     *
     * @param \DateTime $validUntil
     *
     * @return MembershipValidity
     */
    public function setValidUntil($validUntil)
    {
        $this->validUntil = $validUntil;

        return $this;
    }

    /**
     * Get validUntil
     *
     * @return \DateTime
     */
    public function getValidUntil()
    {
        return $this->validUntil;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return MembershipValidity
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
}
