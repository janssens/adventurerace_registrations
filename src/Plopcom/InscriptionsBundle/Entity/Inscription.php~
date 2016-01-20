<?php
// src/Plopcom/InscriptionsBundle/Entity/Inscription.php

namespace Plopcom\InscriptionsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="inscription")
 * @ORM\HasLifecycleCallbacks()
 */
class Inscription
{

    const MYSALT = 'secretsalthahaha';

    const STATUS_UNCHECKED = 2;
    const STATUS_VALID = 1;
    const STATUS_UNVALID = 0;

    const PAYEMENT_STATUS_WAITING = 3;
    const PAYEMENT_STATUS_NOT_PAYED = 2;
    const PAYEMENT_STATUS_PAYED = 1;
    const PAYEMENT_STATUS_FAILED = 0;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $title;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $admin_comment;

    /**
     * @ORM\Column(type="integer")
     */
    protected $status;

    /**
     * @ORM\Column(type="integer")
     */
    protected $payement_status;

    /**
     * @ORM\OneToMany(targetEntity="Athlete", mappedBy="inscription",cascade={"persist"})
     */
    protected $athletes;

    /**
     * @ORM\ManyToOne(targetEntity="Race", inversedBy="inscriptions", cascade={"persist", "merge"})
     * @ORM\JoinColumns({
     *  @ORM\JoinColumn(name="race_id", referencedColumnName="id")
     * })
     */
    protected $race;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    public function __construct()
    {
        $this->athletes = new ArrayCollection();

        $this->setCreated(new \DateTime());
        $this->setUpdated(new \DateTime());
    }

    /**
     * @ORM\preUpdate
     */
    public function setUpdatedValue()
    {
        $this->setUpdated(new \DateTime());
    }


    public function getSalt(){
        return md5(self::MYSALT . '{' . $this->id . '}'); //funny salt, isn't it?
    }

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
     * Set title
     *
     * @param string $title
     *
     * @return Inscription
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
     * Set status
     *
     * @param integer $status
     *
     * @return Inscription
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set payementStatus
     *
     * @param integer $payementStatus
     *
     * @return Inscription
     */
    public function setPayementStatus($payementStatus)
    {
        $this->payement_status = $payementStatus;

        return $this;
    }

    /**
     * Get payementStatus
     *
     * @return integer
     */
    public function getPayementStatus()
    {
        return $this->payement_status;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Inscription
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Inscription
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Add athlete
     *
     * @param \Plopcom\InscriptionsBundle\Entity\Athlete $athlete
     *
     * @return Inscription
     */
    public function addAthlete(\Plopcom\InscriptionsBundle\Entity\Athlete $athlete)
    {
        $this->athletes[] = $athlete;

        return $this;
    }

    /**
     * Remove athlete
     *
     * @param \Plopcom\InscriptionsBundle\Entity\Athlete $athlete
     */
    public function removeAthlete(\Plopcom\InscriptionsBundle\Entity\Athlete $athlete)
    {
        $this->athletes->removeElement($athlete);
    }

    /**
     * Get athletes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAthletes()
    {
        return $this->athletes;
    }

    /**
     * Set race
     *
     * @param \Plopcom\InscriptionsBundle\Entity\Race $race
     *
     * @return Inscription
     */
    public function setRace(\Plopcom\InscriptionsBundle\Entity\Race $race = null)
    {
        $this->race = $race;

        return $this;
    }

    /**
     * Get race
     *
     * @return \Plopcom\InscriptionsBundle\Entity\Race
     */
    public function getRace()
    {
        return $this->race;
    }

    /**
     * Set adminComment
     *
     * @param string $adminComment
     *
     * @return Inscription
     */
    public function setAdminComment($adminComment)
    {
        $this->admin_comment = $adminComment;

        return $this;
    }

    /**
     * Get adminComment
     *
     * @return string
     */
    public function getAdminComment()
    {
        return $this->admin_comment;
    }
}
