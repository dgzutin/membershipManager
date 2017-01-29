<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 22.01.17
 * Time: 13:17
 */
namespace App\Entity;
/**
 * @Entity @Table(name="newsletterArticle")
 **/

class NewsletterArticle
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    protected $id;

    /** @Column(type="integer", nullable=false) * */
    protected $newsletterId;

    /** @Column(type="integer", nullable=false) * */
    protected $userId;

    /** @Column(type="datetime", nullable=false) * */
    protected $createDate;

    /** @Column(type="datetime", nullable=true) * */
    protected $assignedDate;

    /** @Column(type="integer", nullable=true) * */
    protected $articleOrder;

    /** @Column(type="string", length=200, nullable=true) * */
    public $title;

    /** @Column(type="string", length=200, nullable=false) * */
    public $author;

    /** @Column(type="text", nullable=true) * */
    protected $text;

    /** @Column(type="string", length=200, nullable=true) * */
    public $imageUrl;

    /** @Column(type="string", length=150, nullable=true) * */
    public $imageFileName;

    /** @Column(type="string", length=200, nullable=false) * */
    public $moreInfoUrl;

    /** @Column(type="text", nullable=true) * */
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
     * Set newsletterId
     *
     * @param integer $newsletterId
     *
     * @return NewsletterArticle
     */
    public function setNewsletterId($newsletterId)
    {
        $this->newsletterId = $newsletterId;

        return $this;
    }

    /**
     * Get newsletterId
     *
     * @return integer
     */
    public function getNewsletterId()
    {
        return $this->newsletterId;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     *
     * @return NewsletterArticle
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
     * Set createDate
     *
     * @param \DateTime $createDate
     *
     * @return NewsletterArticle
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;

        return $this;
    }

    /**
     * Get createDate
     *
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * Set assignedDate
     *
     * @param \DateTime $assignedDate
     *
     * @return NewsletterArticle
     */
    public function setAssignedDate($assignedDate)
    {
        $this->assignedDate = $assignedDate;

        return $this;
    }

    /**
     * Get assignedDate
     *
     * @return \DateTime
     */
    public function getAssignedDate()
    {
        return $this->assignedDate;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return NewsletterArticle
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
     * Set text
     *
     * @param string $text
     *
     * @return NewsletterArticle
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set imageUrl
     *
     * @param string $imageUrl
     *
     * @return NewsletterArticle
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    /**
     * Get imageUrl
     *
     * @return string
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * Set moreInfoUrl
     *
     * @param string $moreInfoUrl
     *
     * @return NewsletterArticle
     */
    public function setMoreInfoUrl($moreInfoUrl)
    {
        $this->moreInfoUrl = $moreInfoUrl;

        return $this;
    }

    /**
     * Get moreInfoUrl
     *
     * @return string
     */
    public function getMoreInfoUrl()
    {
        return $this->moreInfoUrl;
    }

    /**
     * Set comments
     *
     * @param string $comments
     *
     * @return NewsletterArticle
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
     * Set imageFileName
     *
     * @param string $imageFileName
     *
     * @return NewsletterArticle
     */
    public function setImageFileName($imageFileName)
    {
        $this->imageFileName = $imageFileName;

        return $this;
    }

    /**
     * Get imageFileName
     *
     * @return string
     */
    public function getImageFileName()
    {
        return $this->imageFileName;
    }

    /**
     * Set articleOrder
     *
     * @param integer $articleOrder
     *
     * @return NewsletterArticle
     */
    public function setArticleOrder($articleOrder)
    {
        $this->articleOrder = $articleOrder;

        return $this;
    }

    /**
     * Get articleOrder
     *
     * @return integer
     */
    public function getArticleOrder()
    {
        return $this->articleOrder;
    }

    /**
     * Set author
     *
     * @param string $author
     *
     * @return NewsletterArticle
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }
}
