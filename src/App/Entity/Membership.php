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

    /** @Column(type="integer", length=50, nullable=false) **/
    protected $memberIds;

    /** @Column(type="integer", length=50, nullable=false) **/
    protected $membershipType;

    /** @Column(type="string", length=50, nullable=false) * */
    protected $memberRegDate;



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
     * @param string $membershipType
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
     * @return string
     */
    public function getMembershipType()
    {
        return $this->membershipType;
    }

    /**
     * Set memberIds
     *
     * @param integer $memberIds
     *
     * @return Membership
     */
    public function setMemberIds($memberIds)
    {
        $this->memberIds = $memberIds;

        return $this;
    }

    /**
     * Get memberIds
     *
     * @return integer
     */
    public function getMemberIds()
    {
        return $this->memberIds;
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
}
