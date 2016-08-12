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
    protected $memberId;

    /** @Column(type="boolean", nullable=false) * */
    protected $isOwner;

    /** @Column(type="string", length=50, nullable=false) * */
    protected $createDate;



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
}
