<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 22.01.17
 * Time: 12:55
 */

namespace App\Entity;
/**
 * @Entity @Table(name="newsletter")
 **/

class Newsletter
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    protected $id;

    /** @Column(type="boolean", nullable=false) * */
    protected $published;

    /** @Column(type="datetime", nullable=false) * */
    protected $createDate;

    /** @Column(type="datetime", nullable=true) * */
    protected $publishDate;

    /** @Column(type="text", nullable=true) **/
    protected $introduction;

    /** @Column(type="string", length=200, nullable=false) **/
    public $title;

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
     * Set published
     *
     * @param boolean $published
     *
     * @return Newsletter
     */
    public function setPublished($published)
    {
        $this->published = $published;

        return $this;
    }

    /**
     * Get published
     *
     * @return boolean
     */
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * Set createDate
     *
     * @param \DateTime $createDate
     *
     * @return Newsletter
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
     * Set publishDate
     *
     * @param \DateTime $publishDate
     *
     * @return Newsletter
     */
    public function setPublishDate($publishDate)
    {
        $this->publishDate = $publishDate;

        return $this;
    }

    /**
     * Get publishDate
     *
     * @return \DateTime
     */
    public function getPublishDate()
    {
        return $this->publishDate;
    }

    /**
     * Set introduction
     *
     * @param string $introduction
     *
     * @return Newsletter
     */
    public function setIntroduction($introduction)
    {
        $this->introduction = $introduction;

        return $this;
    }

    /**
     * Get introduction
     *
     * @return string
     */
    public function getIntroduction()
    {
        return $this->introduction;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Newsletter
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
     * Set comments
     *
     * @param string $comments
     *
     * @return Newsletter
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
}
