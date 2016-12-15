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

    /** @Column(type="integer", nullable=false) **/
    protected $listingOrder;


    /** @Column(type="string", length=50, nullable=false) * */
    protected $typeName;

    /** @Column(type="string", length=50, nullable=false) * */
    protected $typeAlias;

    /** @Column(type="string", length=10, nullable=true) * */
    protected $prefix;

    /** @Column(type="boolean", length=50, nullable=false) * */
    protected $useGlobalMemberNumberAssignment;

    /** @Column(type="integer",  nullable=true) **/
    protected $initialMemberId; //used only once, if useGlobalMemberNumberAssignment is FALSE

    /** @Column(type="boolean", length=50, nullable=false) * */
    protected $selectable;

    /** @Column(type="decimal", precision=7, scale=2) * */
    protected $fee;

    /** @Column(type="string", length=10, nullable=true) * */
    protected $renewal_threshold;

    /** @Column(type="string", length=10, nullable=true) * */
    protected $currency;

    /** @Column(type="string", length=20, nullable=true) * */
    protected $recurrence;

    /** @Column(type="string", length=500, nullable=true) **/
    protected $description;

    /** @Column(type="text", nullable=true) **/
    protected $terms;

    /** @Column(type="integer", nullable=false) **/
    protected $numberOfRepresentatives;

    

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

    /**
     * Set description
     *
     * @param string $description
     *
     * @return MembershipType
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
     * Set currency
     *
     * @param string $currency
     *
     * @return MembershipType
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }
    
    


    /**
     * Set numberOfRepresentatives
     *
     * @param integer $numberOfRepresentatives
     *
     * @return MembershipType
     */
    public function setNumberOfRepresentatives($numberOfRepresentatives)
    {
        $this->numberOfRepresentatives = $numberOfRepresentatives;

        return $this;
    }

    /**
     * Get numberOfRepresentatives
     *
     * @return integer
     */
    public function getNumberOfRepresentatives()
    {
        return $this->numberOfRepresentatives;
    }

    /**
     * Set prefix
     *
     * @param string $prefix
     *
     * @return MembershipType
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Get prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set useGlobalMemberNumberAssignment
     *
     * @param boolean $useGlobalMemberNumberAssignment
     *
     * @return MembershipType
     */
    public function setUseGlobalMemberNumberAssignment($useGlobalMemberNumberAssignment)
    {
        $this->useGlobalMemberNumberAssignment = $useGlobalMemberNumberAssignment;

        return $this;
    }

    /**
     * Get useGlobalMemberNumberAssignment
     *
     * @return boolean
     */
    public function getUseGlobalMemberNumberAssignment()
    {
        return $this->useGlobalMemberNumberAssignment;
    }

    /**
     * Set terms
     *
     * @param string $terms
     *
     * @return MembershipType
     */
    public function setTerms($terms)
    {
        $this->terms = $terms;

        return $this;
    }

    /**
     * Get terms
     *
     * @return string
     */
    public function getTerms()
    {
        return $this->terms;
    }

    /**
     * Set initialMemberId
     *
     * @param integer $initialMemberId
     *
     * @return MembershipType
     */
    public function setInitialMemberId($initialMemberId)
    {
        $this->initialMemberId = $initialMemberId;

        return $this;
    }

    /**
     * Get initialMemberId
     *
     * @return integer
     */
    public function getInitialMemberId()
    {
        return $this->initialMemberId;
    }

    /**
     * Set listingOrder
     *
     * @param integer $listingOrder
     *
     * @return MembershipType
     */
    public function setListingOrder($listingOrder)
    {
        $this->listingOrder = $listingOrder;

        return $this;
    }

    /**
     * Get listingOrder
     *
     * @return integer
     */
    public function getListingOrder()
    {
        return $this->listingOrder;
    }

    /**
     * Set renewalThreshold
     *
     * @param string $renewalThreshold
     *
     * @return MembershipType
     */
    public function setRenewalThreshold($renewalThreshold)
    {
        $this->renewal_threshold = $renewalThreshold;

        return $this;
    }

    /**
     * Get renewalThreshold
     *
     * @return string
     */
    public function getRenewalThreshold()
    {
        return $this->renewal_threshold;
    }
}
