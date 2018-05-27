<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 15.05.16
 * Time: 19:53
 */

// Entity/User.php
namespace App\Entity;
/**
 * @Entity @Table(name="user")
 **/

class User
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    public $id;

    /** @Column(type="string", length=100, nullable=false) **/
    protected $password;

    /** @Column(type="string", length=50, nullable=false) **/
    protected $role;

    /** @Column(type="string", length=50, nullable=true) **/
    public $linkedin_id;

    /** @Column(type="string", length=50, nullable=false) **/
    public $first_name;

    /** @Column(type="string", length=50, nullable=false) **/
    public $last_name;

    /** @Column(type="string", length=10, nullable=true) **/
    public $title;

    /** @Column(type="string", length=50, nullable=true) **/
    public $position;

    /** @Column(type="string", length=100, nullable=false) **/
    public $institution;

    /** @Column(type="string", length=100, nullable=true) **/
    public $department;

    /** @Column(type="string", length=100, nullable=true) **/
    public $street;

    /** @Column(type="string", length=75, nullable=true) **/
    public $city;

    /** @Column(type="string", length=50, nullable=true) **/
    public $zip;

    /** @Column(type="string", length=10, nullable=false) **/
    public $country;

    /** @Column(type="string", length=100, nullable=false) **/
    public $email_1;

    /** @Column(type="string", length=100, nullable=true) **/
    public $email_2;

    /** @Column(type="string", length=300, nullable=true) **/
    public $website;

    /** @Column(type="string", length=300, nullable=true) **/
    public $pictureUrl;

    /** @Column(type="string", length=100, nullable=true) **/
    public $phone;

    /** @Column(type="text", nullable=true) **/
    protected $comments;

    /** @Column(type="datetime", nullable=true) * */
    protected $userRegDate;

    /** @Column(type="string", length=100, nullable=true) * */
    public $profileKey;

    /** @Column(type="boolean", nullable=false) * */
    protected $active;

    /** @Column(type="boolean", nullable=true) * */
    protected $generalTermsConsent;

    /** @Column(type="boolean", nullable=true) * */
    public $newsletterConsent;

    /** @Column(type="boolean", nullable=true) * */
    public $membershipEmailConsent;

    /** @Column(type="boolean", nullable=true) * */
    public $societyEmailConsent;



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
     * Set firstName
     *
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return User
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set position
     *
     * @param string $position
     *
     * @return User
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set institution
     *
     * @param string $institution
     *
     * @return User
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
     * Set department
     *
     * @param string $department
     *
     * @return User
     */
    public function setDepartment($department)
    {
        $this->department = $department;

        return $this;
    }

    /**
     * Get department
     *
     * @return string
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * Set streetAddress
     *
     * @param string $streetAddress
     *
     * @return User
     */
    public function setStreetAddress($streetAddress)
    {
        $this->street_address = $streetAddress;

        return $this;
    }

    /**
     * Get streetAddress
     *
     * @return string
     */
    public function getStreetAddress()
    {
        return $this->street_address;
    }

    /**
     * Set cityAddress
     *
     * @param string $cityAddress
     *
     * @return User
     */
    public function setCityAddress($cityAddress)
    {
        $this->city_address = $cityAddress;

        return $this;
    }

    /**
     * Get cityAddress
     *
     * @return string
     */
    public function getCityAddress()
    {
        return $this->city_address;
    }

    /**
     * Set zipAddress
     *
     * @param string $zipAddress
     *
     * @return User
     */
    public function setZipAddress($zipAddress)
    {
        $this->zip_address = $zipAddress;

        return $this;
    }

    /**
     * Get zipAddress
     *
     * @return string
     */
    public function getZipAddress()
    {
        return $this->zip_address;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return User
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
     * Set email1
     *
     * @param string $email1
     *
     * @return User
     */
    public function setEmail1($email1)
    {
        $this->email_1 = $email1;

        return $this;
    }

    /**
     * Get email1
     *
     * @return string
     */
    public function getEmail1()
    {
        return $this->email_1;
    }

    /**
     * Set email2
     *
     * @param string $email2
     *
     * @return User
     */
    public function setEmail2($email2)
    {
        $this->email_2 = $email2;

        return $this;
    }

    /**
     * Get email2
     *
     * @return string
     */
    public function getEmail2()
    {
        return $this->email_2;
    }

    /**
     * Set website
     *
     * @param string $website
     *
     * @return User
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return User
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
     * Set fax
     *
     * @param string $fax
     *
     * @return User
     */
    public function setFax($fax)
    {
        $this->fax = $fax;

        return $this;
    }

    /**
     * Get fax
     *
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set street
     *
     * @param string $street
     *
     * @return User
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
     * @return User
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
     * @return User
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
     * Set comments
     *
     * @param string $comments
     *
     * @return User
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
     * Set userRegDate
     *
     * @param string $userRegDate
     *
     * @return User
     */
    public function setUserRegDate($userRegDate)
    {
        $this->userRegDate = $userRegDate;

        return $this;
    }

    /**
     * Get userRegDate
     *
     * @return string
     */
    public function getUserRegDate()
    {
        return $this->userRegDate;
    }

    /**
     * Set role
     *
     * @param string $role
     *
     * @return User
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set Active
     *
     * @param string $active
     *
     * @return User
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get Active
     *
     * @return string
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set $profilekey
     *
     * @param string $profilekey
     *
     * @return User
     */
    public function setProfileKey($profileKey)
    {
        $this->profileKey = $profileKey;

        return $this;
    }

    /**
     * Get $profilekey
     *
     * @return string
     */
    public function getProfileKey()
    {
        return $this->profileKey;
    }



    /**
     * Set linkedinId
     *
     * @param string $linkedinId
     *
     * @return User
     */
    public function setLinkedinId($linkedinId)
    {
        $this->linkedin_id = $linkedinId;

        return $this;
    }

    /**
     * Get linkedinId
     *
     * @return string
     */
    public function getLinkedinId()
    {
        return $this->linkedin_id;
    }

    /**
     * Set pictureUrl
     *
     * @param string $pictureUrl
     *
     * @return User
     */
    public function setPictureUrl($pictureUrl)
    {
        $this->pictureUrl = $pictureUrl;

        return $this;
    }

    /**
     * Get pictureUrl
     *
     * @return string
     */
    public function getPictureUrl()
    {
        return $this->pictureUrl;
    }

    /**
     * Set generalTermsConsent
     *
     * @param boolean $generalTermsConsent
     *
     * @return User
     */
    public function setGeneralTermsConsent($generalTermsConsent)
    {
        $this->generalTermsConsent = $generalTermsConsent;

        return $this;
    }

    /**
     * Get generalTermsConsent
     *
     * @return boolean
     */
    public function getGeneralTermsConsent()
    {
        return $this->generalTermsConsent;
    }

    /**
     * Set newsletterConsent
     *
     * @param boolean $newsletterConsent
     *
     * @return User
     */
    public function setNewsletterConsent($newsletterConsent)
    {
        $this->newsletterConsent = $newsletterConsent;

        return $this;
    }

    /**
     * Get newsletterConsent
     *
     * @return boolean
     */
    public function getNewsletterConsent()
    {
        return $this->newsletterConsent;
    }

    /**
     * Set membershipEmailConsent
     *
     * @param boolean $membershipEmailConsent
     *
     * @return User
     */
    public function setMembershipEmailConsent($membershipEmailConsent)
    {
        $this->membershipEmailConsent = $membershipEmailConsent;

        return $this;
    }

    /**
     * Get membershipEmailConsent
     *
     * @return boolean
     */
    public function getMembershipEmailConsent()
    {
        return $this->membershipEmailConsent;
    }

    /**
     * Set societyEmailConsent
     *
     * @param boolean $societyEmailConsent
     *
     * @return User
     */
    public function setSocietyEmailConsent($societyEmailConsent)
    {
        $this->societyEmailConsent = $societyEmailConsent;

        return $this;
    }

    /**
     * Get societyEmailConsent
     *
     * @return boolean
     */
    public function getSocietyEmailConsent()
    {
        return $this->societyEmailConsent;
    }
}
