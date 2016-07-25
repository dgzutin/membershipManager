<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 24.05.16
 * Time: 11:30
 */

namespace App\Entity;
/**
 * @Entity @Table(name="membershipType")
 **/

class MembershipType
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    protected $id;

    /** @Column(type="string", length=50, nullable=false) * */
    protected $typeName;

    /** @Column(type="string", length=50, nullable=false) * */
    protected $typeAlias;

    /** @Column(type="boolean", length=50, nullable=false) * */
    protected $selectable;

    /** @Column(type="decimal", length=10, nullable=true) * */
    protected $fee;

    /** @Column(type="string", length=20, nullable=true) * */
    protected $recurrence;

    

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
     * Set typeName
     *
     * @param integer $typeName
     *
     * @return MembershipType
     */
    public function setTypeName($typeName)
    {
        $this->typeName = $typeName;

        return $this;
    }

    /**
     * Get typeName
     *
     * @return integer
     */
    public function getTypeName()
    {
        return $this->typeName;
    }


    /**
     * Set fee
     *
     * @param string $fee
     *
     * @return MembershipType
     */
    public function setFee($fee)
    {
        $this->fee = $fee;

        return $this;
    }

    /**
     * Get fee
     *
     * @return string
     */
    public function getFee()
    {
        return $this->fee;
    }

    /**
     * Set selectable
     *
     * @param boolean $selectable
     *
     * @return MembershipType
     */
    public function setSelectable($selectable)
    {
        $this->selectable = $selectable;

        return $this;
    }

    /**
     * Get selectable
     *
     * @return boolean
     */
    public function getSelectable()
    {
        return $this->selectable;
    }

    /**
     * Set typeAlias
     *
     * @param integer $typeAlias
     *
     * @return MembershipType
     */
    public function setTypeAlias($typeAlias)
    {
        $this->typeAlias = $typeAlias;

        return $this;
    }

    /**
     * Get typeAlias
     *
     * @return integer
     */
    public function getTypeAlias()
    {
        return $this->typeAlias;
    }

    /**
     * Set recurrence
     *
     * @param string $recurrence
     *
     * @return MembershipType
     */
    public function setRecurrence($recurrence)
    {
        $this->recurrence = $recurrence;

        return $this;
    }

    /**
     * Get recurrence
     *
     * @return string
     */
    public function getRecurrence()
    {
        return $this->recurrence;
    }
}
