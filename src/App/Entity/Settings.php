<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 13.08.16
 * Time: 13:51
 */

namespace App\Entity;
/**
 * @Entity @Table(name="settings")
 **/

class Settings
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    protected $id;

    /** @Column(type="string", length=100, nullable=false) * */
    protected $nameOfOrganization;

    /** @Column(type="string", length=20, nullable=false) * */
    protected $acronym;

    /** @Column(type="string", length=50, nullable=false) * */
    protected $email;

    /** @Column(type="string", length=50, nullable=false) * */
    protected $phone;

    /** @Column(type="string", length=50, nullable=false) * */
    protected $orgWebsite;

    /** @Column(type="string", length=50, nullable=true) **/
    protected $street;

    /** @Column(type="string", length=50, nullable=true) **/
    protected $city;

    /** @Column(type="string", length=20, nullable=true) **/
    protected $zip;

    /** @Column(type="string", length=50, nullable=true) **/
    protected $country;

    /** @Column(type="string", length=50, nullable=true) **/
    protected $vat;

    /** @Column(type="decimal", nullable=true) **/
    protected $vat_rate;

    /** @Column(type="string", length=50, nullable=true) **/
    protected $registrationNumber;

    /** @Column(type="text", nullable=true) **/
    protected $disclaimer;
    

    // Payment Settings

    /** @Column(type="string", length=10, nullable=false) * */
    protected $systemCurrency;

    /** @Column(type="boolean", nullable=false) **/
    protected $paypalActive;

    /** @Column(type="boolean", nullable=false) **/
    protected $paypalSandboxModeActive;

    /** @Column(type="string", length=50, nullable=true) **/
    protected $paypalEmail;

    /** @Column(type="boolean", nullable=false) **/
    protected $wireTransferActive;

    /** @Column(type="string", length=100, nullable=true) **/
    protected $iban;

    /** @Column(type="string", length=100, nullable=true) **/
    protected $bic;

    /** @Column(type="string", length=300, nullable=true) **/
    protected $bankName;

    /** @Column(type="string", length=100, nullable=true) **/
    protected $bankAddress;

    /** @Column(type="string", length=50, nullable=true) **/
    protected $googleAnalyticsTrackingId;

    /** @Column(type="boolean", nullable=true) **/
    protected $enableOauthLinkedIn;

    /** @Column(type="string", length=50, nullable=true) **/
    protected $linkedInClientId;

    /** @Column(type="string", length=50, nullable=true) **/
    protected $linkedInClientSecret;


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
     * Set nameOfOrganization
     *
     * @param string $nameOfOrganization
     *
     * @return Settings
     */
    public function setNameOfOrganization($nameOfOrganization)
    {
        $this->nameOfOrganization = $nameOfOrganization;

        return $this;
    }

    /**
     * Get nameOfOrganization
     *
     * @return string
     */
    public function getNameOfOrganization()
    {
        return $this->nameOfOrganization;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Settings
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set street
     *
     * @param string $street
     *
     * @return Settings
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
     * @return Settings
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
     * @return Settings
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
     * @return Settings
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
     * @return Settings
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
     * Set registrationNumber
     *
     * @param string $registrationNumber
     *
     * @return Settings
     */
    public function setRegistrationNumber($registrationNumber)
    {
        $this->registrationNumber = $registrationNumber;

        return $this;
    }

    /**
     * Get registrationNumber
     *
     * @return string
     */
    public function getRegistrationNumber()
    {
        return $this->registrationNumber;
    }

    /**
     * Set paypalActive
     *
     * @param boolean $paypalActive
     *
     * @return Settings
     */
    public function setPaypalActive($paypalActive)
    {
        $this->paypalActive = $paypalActive;

        return $this;
    }

    /**
     * Get paypalActive
     *
     * @return boolean
     */
    public function getPaypalActive()
    {
        return $this->paypalActive;
    }

    /**
     * Set paypalEmail
     *
     * @param string $paypalEmail
     *
     * @return Settings
     */
    public function setPaypalEmail($paypalEmail)
    {
        $this->paypalEmail = $paypalEmail;

        return $this;
    }

    /**
     * Get paypalEmail
     *
     * @return string
     */
    public function getPaypalEmail()
    {
        return $this->paypalEmail;
    }

    /**
     * Set wireTransferActive
     *
     * @param boolean $wireTransferActive
     *
     * @return Settings
     */
    public function setWireTransferActive($wireTransferActive)
    {
        $this->wireTransferActive = $wireTransferActive;

        return $this;
    }

    /**
     * Get wireTransferActive
     *
     * @return boolean
     */
    public function getWireTransferActive()
    {
        return $this->wireTransferActive;
    }

    /**
     * Set iban
     *
     * @param string $iban
     *
     * @return Settings
     */
    public function setIban($iban)
    {
        $this->iban = $iban;

        return $this;
    }

    /**
     * Get iban
     *
     * @return string
     */
    public function getIban()
    {
        return $this->iban;
    }

    /**
     * Set bic
     *
     * @param string $bic
     *
     * @return Settings
     */
    public function setBic($bic)
    {
        $this->bic = $bic;

        return $this;
    }

    /**
     * Get bic
     *
     * @return string
     */
    public function getBic()
    {
        return $this->bic;
    }

    /**
     * Set bankAddress
     *
     * @param string $bankAddress
     *
     * @return Settings
     */
    public function setBankAddress($bankAddress)
    {
        $this->bankAddress = $bankAddress;

        return $this;
    }

    /**
     * Get bankAddress
     *
     * @return string
     */
    public function getBankAddress()
    {
        return $this->bankAddress;
    }

    /**
     * Set orgWebsite
     *
     * @param string $orgWebsite
     *
     * @return Settings
     */
    public function setOrgWebsite($orgWebsite)
    {
        $this->orgWebsite = $orgWebsite;

        return $this;
    }

    /**
     * Get orgWebsite
     *
     * @return string
     */
    public function getOrgWebsite()
    {
        return $this->orgWebsite;
    }

    /**
     * Set systemCurrency
     *
     * @param string $systemCurrency
     *
     * @return Settings
     */
    public function setSystemCurrency($systemCurrency)
    {
        $this->systemCurrency = $systemCurrency;

        return $this;
    }

    /**
     * Get systemCurrency
     *
     * @return string
     */
    public function getSystemCurrency()
    {
        return $this->systemCurrency;
    }

    /**
     * Set paypalSandboxModeActive
     *
     * @param boolean $paypalSandboxModeActive
     *
     * @return Settings
     */
    public function setPaypalSandboxModeActive($paypalSandboxModeActive)
    {
        $this->paypalSandboxModeActive = $paypalSandboxModeActive;

        return $this;
    }

    /**
     * Get paypalSandboxModeActive
     *
     * @return boolean
     */
    public function getPaypalSandboxModeActive()
    {
        return $this->paypalSandboxModeActive;
    }

    /**
     * Set acronym
     *
     * @param string $acronym
     *
     * @return Settings
     */
    public function setAcronym($acronym)
    {
        $this->acronym = $acronym;

        return $this;
    }

    /**
     * Get acronym
     *
     * @return string
     */
    public function getAcronym()
    {
        return $this->acronym;
    }

    /**
     * Set disclaimer
     *
     * @param string $disclaimer
     *
     * @return Settings
     */
    public function setDisclaimer($disclaimer)
    {
        $this->disclaimer = $disclaimer;

        return $this;
    }

    /**
     * Get disclaimer
     *
     * @return string
     */
    public function getDisclaimer()
    {
        return $this->disclaimer;
    }

    /**
     * Set vatRate
     *
     * @param string $vatRate
     *
     * @return Settings
     */
    public function setVatRate($vatRate)
    {
        $this->vat_rate = $vatRate;

        return $this;
    }

    /**
     * Get vatRate
     *
     * @return string
     */
    public function getVatRate()
    {
        return $this->vat_rate;
    }

    /**
     * Set bankName
     *
     * @param string $bankName
     *
     * @return Settings
     */
    public function setBankName($bankName)
    {
        $this->bankName = $bankName;

        return $this;
    }

    /**
     * Get bankName
     *
     * @return string
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return Settings
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set googleAnalyticsTrackingId
     *
     * @param string $googleAnalyticsTrackingId
     *
     * @return Settings
     */
    public function setGoogleAnalyticsTrackingId($googleAnalyticsTrackingId)
    {
        $this->googleAnalyticsTrackingId = $googleAnalyticsTrackingId;

        return $this;
    }

    /**
     * Get googleAnalyticsTrackingId
     *
     * @return string
     */
    public function getGoogleAnalyticsTrackingId()
    {
        return $this->googleAnalyticsTrackingId;
    }

    /**
     * Set enableOauthLinkedIn
     *
     * @param boolean $enableOauthLinkedIn
     *
     * @return Settings
     */
    public function setEnableOauthLinkedIn($enableOauthLinkedIn)
    {
        $this->enableOauthLinkedIn = $enableOauthLinkedIn;

        return $this;
    }

    /**
     * Get enableOauthLinkedIn
     *
     * @return boolean
     */
    public function getEnableOauthLinkedIn()
    {
        return $this->enableOauthLinkedIn;
    }

    /**
     * Set linkedInClientId
     *
     * @param string $linkedInClientId
     *
     * @return Settings
     */
    public function setLinkedInClientId($linkedInClientId)
    {
        $this->linkedInClientId = $linkedInClientId;

        return $this;
    }

    /**
     * Get linkedInClientId
     *
     * @return string
     */
    public function getLinkedInClientId()
    {
        return $this->linkedInClientId;
    }

    /**
     * Set linkedInClientSecret
     *
     * @param string $linkedInClientSecret
     *
     * @return Settings
     */
    public function setLinkedInClientSecret($linkedInClientSecret)
    {
        $this->linkedInClientSecret = $linkedInClientSecret;

        return $this;
    }

    /**
     * Get linkedInClientSecret
     *
     * @return string
     */
    public function getLinkedInClientSecret()
    {
        return $this->linkedInClientSecret;
    }
    
}
