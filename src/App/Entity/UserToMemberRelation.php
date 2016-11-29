<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 11.08.16
 * Time: 18:22
 */

namespace App\Entity;
/**
 * @Entity @Table(name="UserToMemberRelation")
 **/

class UserToMemberRelation
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    protected $id;

    /** @Column(type="integer",  nullable=false) * */
    protected $userId;

    /** @Column(type="integer",  nullable=false) * */
    protected $membershipId;

    /** @Column(type="integer",  nullable=false) * */
    protected $membershipTypeId;

    /** @Column(type="datetime", nullable=false) * */
    protected $createDate;

    /** @Column(type="boolean", nullable=false) * */
    protected $isOwner;


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
     * @return UserToMemberRelation
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
     * Set memberId
     *
     * @param integer $memberId
     *
     * @return UserToMemberRelation
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
     * Set createDate
     *
     * @param string $createDate
     *
     * @return UserToMemberRelation
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
     * Set membershipTypeId
     *
     * @param integer $membershipTypeId
     *
     * @return UserToMemberRelation
     */
    public function setMembershipTypeId($membershipTypeId)
    {
        $this->membershipTypeId = $membershipTypeId;

        return $this;
    }

    /**
     * Get membershipTypeId
     *
     * @return integer
     */
    public function getMembershipTypeId()
    {
        return $this->membershipTypeId;
    }

    /**
     * Set isOwner
     *
     * @param boolean $isOwner
     *
     * @return UserToMemberRelation
     */
    public function setIsOwner($isOwner)
    {
        $this->isOwner = $isOwner;

        return $this;
    }

    /**
     * Get isOwner
     *
     * @return boolean
     */
    public function getIsOwner()
    {
        return $this->isOwner;
    }

    /**
     * Set membershipId
     *
     * @param integer $membershipId
     *
     * @return UserToMemberRelation
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
}
