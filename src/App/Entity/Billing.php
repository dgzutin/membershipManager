<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 23.05.16
 * Time: 13:57
 */

namespace App\Entity;
/**
 * @Entity @Table(name="billing")
 **/

class Billing
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    protected $id;

    /** @Column(type="integer", length=50, nullable=false) **/
    protected $userId;

    /** @Column(type="string", length=50, nullable=true) * */
    protected $typeAlias;

    // ----- Billing Address

    /** @Column(type="string", length=50, nullable=true) **/
    public $name;

    /** @Column(type="string", length=50, nullable=true) **/
    public $institution;

    /** @Column(type="string", length=50, nullable=true) **/
    public $street;

    /** @Column(type="string", length=50, nullable=true) **/
    public $city;

    /** @Column(type="string", length=20, nullable=true) **/
    public $zip;

    /** @Column(type="string", length=50, nullable=true) **/
    public $country;

    /** @Column(type="string", length=50, nullable=true) **/
    public $vat;

    /** @Column(type="string", length=50, nullable=true) **/
    public $reference;



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
     * @return Billing
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
     * Set name
     *
     * @param string $name
     *
     * @return Billing
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set institution
     *
     * @param string $institution
     *
     * @return Billing
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;

        return $this;
    }

    /**
     * Get institution
     *
     * @return string
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * Set street
     *
     * @param string $street
     *
     * @return Billing
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return Billing
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set zip
     *
     * @param string $zip
     *
     * @return Billing
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get zip
     *
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return Billing
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set vat
     *
     * @param string $vat
     *
     * @return Billing
     */
    public function setVat($vat)
    {
        $this->vat = $vat;

        return $this;
    }

    /**
     * Get vat
     *
     * @return string
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * Set reference
     *
     * @param string $reference
     *
     * @return Billing
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get reference
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }
    

    /**
     * Set typeAlias
     *
     * @param string $typeAlias
     *
     * @return Billing
     */
    public function setTypeAlias($typeAlias)
    {
        $this->typeAlias = $typeAlias;

        return $this;
    }

    /**
     * Get typeAlias
     *
     * @return string
     */
    public function getTypeAlias()
    {
        return $this->typeAlias;
    }
}
