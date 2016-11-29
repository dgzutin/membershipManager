<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 24.11.16
 * Time: 11:30
 */

namespace App\Entity;
/**
 * @Entity @Table(name="membershipGrade")
 **/

class MembershipGrade
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    protected $id;

    /** @Column(type="string", length=50, nullable=false) * */
    protected $gradeName;

    /** @Column(type="string", length=500, nullable=true) **/
    protected $description;

    /** @Column(type="datetime", nullable=false) * */
    protected $dateCreated;


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
     * Set gradeName
     *
     * @param string $gradeName
     *
     * @return MembershipGrade
     */
    public function setGradeName($gradeName)
    {
        $this->gradeName = $gradeName;

        return $this;
    }

    /**
     * Get gradeName
     *
     * @return string
     */
    public function getGradeName()
    {
        return $this->gradeName;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return MembershipGrade
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
    

    /**
     * Set dateCreated
     *
     * @param string $dateCreated
     *
     * @return MembershipGrade
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * Get dateCreated
     *
     * @return string
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }
}
