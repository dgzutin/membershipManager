<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 10.04.17
 * Time: 14:54
 */

// Entity/userLog.php
namespace App\Entity;
/**
 * @Entity @Table(name="userLog")
 **/

class UserLog
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    public $id;

    /** @Column(type="datetime", nullable=false) * */
    protected $dateTime;

    /** @Column(type="string", length=50, nullable=true) * */
    protected $level;

    /** @Column(type="string", length=50, nullable=true) * */
    protected $channel;

    /** @Column(type="integer", nullable=true) * */
    protected $userId;

    /** @Column(type="string", length=50, nullable=true) * */
    protected $userRole;

    /** @Column(type="integer", nullable=true) * */
    protected $affectedMembershipId;

    /** @Column(type="integer", nullable=true) * */
    protected $type;

    /** @Column(type="text", nullable=true) **/
    protected $message;

    /** @Column(type="text", nullable=true) **/
    protected $context;
    

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
     * @return UserLog
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
     * Set dateTime
     *
     * @param \DateTime $dateTime
     *
     * @return UserLog
     */
    public function setDateTime($dateTime)
    {
        $this->dateTime = $dateTime;

        return $this;
    }

    /**
     * Get dateTime
     *
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * Set level
     *
     * @param string $level
     *
     * @return UserLog
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set channel
     *
     * @param string $channel
     *
     * @return UserLog
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Get channel
     *
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return UserLog
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set userRole
     *
     * @param string $userRole
     *
     * @return UserLog
     */
    public function setUserRole($userRole)
    {
        $this->userRole = $userRole;

        return $this;
    }

    /**
     * Get userRole
     *
     * @return string
     */
    public function getUserRole()
    {
        return $this->userRole;
    }

    /**
     * Set type
     *
     * @param integer $type
     *
     * @return UserLog
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set context
     *
     * @param string $context
     *
     * @return UserLog
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get context
     *
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set affectedMembershipId
     *
     * @param integer $affectedMembershipId
     *
     * @return UserLog
     */
    public function setAffectedMembershipId($affectedMembershipId)
    {
        $this->affectedMembershipId = $affectedMembershipId;

        return $this;
    }

    /**
     * Get affectedMembershipId
     *
     * @return integer
     */
    public function getAffectedMembershipId()
    {
        return $this->affectedMembershipId;
    }
}
