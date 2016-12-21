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
    public $id;

    /** @Column(type="integer",  nullable=false) **/
    public $memberId;

    /** @Column(type="integer",  nullable=false) **/
    protected $ownerId;

    /** @Column(type="integer", nullable=false) **/
    public $membershipTypeId;

    /** @Column(type="datetime", nullable=false) * */
    protected $memberRegDate;

    /** @Column(type="string", length=100, nullable=true) * */
    public $quickRenewKey;

    /** @Column(type="boolean", nullable=false) * */
    protected $cancelled;

    /** @Column(type="text", length=500, nullable=true) **/
    protected $reasonForCancel;

    /** @Column(type="string", length=50, nullable=true) * */
    protected $dateCancelled;

    /** @Column(type="integer", nullable=true) * */
    public $membershipGrade;

    /** @Column(type="text", nullable=true) **/
    protected $comments;


    

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
     * Set cancelled
     *
     * @param boolean $cancelled
     *
     * @return Membership
     */
    public function setCancelled($cancelled)
    {
        $this->cancelled = $cancelled;

        return $this;
    }

    /**
     * Get cancelled
     *
     * @return boolean
     */
    public function getCancelled()
    {
        return $this->cancelled;
    }

    /**
     * Set dateCancelled
     *
     * @param string $dateCancelled
     *
     * @return Membership
     */
    public function setDateCancelled($dateCancelled)
    {
        $this->dateCancelled = $dateCancelled;

        return $this;
    }

    /**
     * Get dateCancelled
     *
     * @return string
     */
    public function getDateCancelled()
    {
        return $this->dateCancelled;
    }

    /**
     * Set comments
     *
     * @param string $comments
     *
     * @return Membership
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Get comments
     *
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set ownerId
     *
     * @param integer $ownerId
     *
     * @return Membership
     */
    public function setOwnerId($ownerId)
    {
        $this->ownerId = $ownerId;

        return $this;
    }

    /**
     * Get ownerId
     *
     * @return integer
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * Set membershipTypeId
     *
     * @param integer $membershipTypeId
     *
     * @return Membership
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
     * Set grade
     *
     * @param string $grade
     *
     * @return Membership
     */
    public function setGrade($grade)
    {
        $this->grade = $grade;

        return $this;
    }

    /**
     * Get grade
     *
     * @return string
     */
    public function getGrade()
    {
        return $this->grade;
    }

    /**
     * Set membershipGrade
     *
     * @param integer $membershipGrade
     *
     * @return Membership
     */
    public function setMembershipGrade($membershipGrade)
    {
        $this->membershipGrade = $membershipGrade;

        return $this;
    }

    /**
     * Get membershipGrade
     *
     * @return integer
     */
    public function getMembershipGrade()
    {
        return $this->membershipGrade;
    }

    /**
     * Set reasonForCancel
     *
     * @param string $reasonForCancel
     *
     * @return Membership
     */
    public function setReasonForCancel($reasonForCancel)
    {
        $this->reasonForCancel = $reasonForCancel;

        return $this;
    }

    /**
     * Get reasonForCancel
     *
     * @return string
     */
    public function getReasonForCancel()
    {
        return $this->reasonForCancel;
    }
}
