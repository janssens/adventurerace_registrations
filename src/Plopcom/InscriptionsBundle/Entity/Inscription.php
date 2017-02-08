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

    const STATUS_DNS = 3;
    const STATUS_UNCHECKED = 2;
    const STATUS_VALID = 1;
    const STATUS_UNVALID = 0;

    const PAYEMENT_STATUS_REFUND = 4;
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
     * @ORM\Column(type="integer")
     */
    protected $position = 10000;

    /**
     * @ORM\Column(type="string",nullable=true)
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
     * @ORM\Column(type="boolean")
     */
    protected $signed;

    /**
     * @ORM\Column(type="integer")
     */
    protected $payement_status;

    /**
     * @ORM\OneToMany(targetEntity="Athlete", mappedBy="inscription",cascade={"persist"})
     */
    protected $athletes;

    /**
     * @ORM\OneToMany(targetEntity="InscriptionOption", mappedBy="inscription", cascade={"persist","remove"})
     */
    protected $options;

    /**
     * @ORM\ManyToOne(targetEntity="Race", inversedBy="inscriptions", cascade={"persist", "merge"})
     * @ORM\JoinColumns({
     *  @ORM\JoinColumn(name="race_id", referencedColumnName="id",onDelete="CASCADE")
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
     * @ORM\PreUpdate()
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

    public function getCategorie(){
        $sum = 0;
        foreach($this->getAthletes() as $athlete){
            $sum += $athlete->getGender();
        }
        $cat = '';
        switch ($sum){
            case $this->getRace()->getNumberOfAthlete()*Athlete::FEMALE :
                $cat = "Féminine";
                break;
            case $this->getRace()->getNumberOfAthlete()*Athlete::MALE :
                $cat = "Masculine";
                break;
            default :
                $cat = "Mixte";
                break;
        }

        return $cat;
    }

    public function getCategorieSign(){
        $sum = 0;
        foreach($this->getAthletes() as $athlete){
            $sum += $athlete->getGender();
        }
        $cat = '';
        switch ($sum){
            case $this->getRace()->getNumberOfAthlete()*Athlete::FEMALE :
                $cat = "<i class=\"fa fa-fw fa-female\"></i>";
                if ($this->getRace()->getNumberOfAthlete()>1)
                    $cat .= "<i class=\"fa fa-fw fa-female\"></i>";
                break;
            case $this->getRace()->getNumberOfAthlete()*Athlete::MALE :
                $cat = "<i class=\"fa fa-fw fa-male\"></i>";
                if ($this->getRace()->getNumberOfAthlete()>1)
                    $cat .= "<i class=\"fa fa-fw fa-male\"></i>";
                break;
            default :
                $cat = "<i class=\"fa fa-fw fa-female\"></i><i class=\"fa fa-fw fa-male\"></i>";
                break;
        }

        return $cat;
    }

    public function getCategorieLetter(){
        $sum = 0;
        foreach($this->getAthletes() as $athlete){
            $sum += $athlete->getGender();
        }
        $cat = '';
        switch ($sum){
            case $this->getRace()->getNumberOfAthlete()*Athlete::FEMALE :
                $cat = "F";
                break;
            case $this->getRace()->getNumberOfAthlete()*Athlete::MALE :
                $cat = "H";
                break;
            default :
                $cat = "Mixte";
                break;
        }

        return $cat;
    }

    public function getCategorieLetterEnglish(){
        $sum = 0;
        foreach($this->getAthletes() as $athlete){
            $sum += $athlete->getGender();
        }
        $cat = '';
        switch ($sum){
            case $this->getRace()->getNumberOfAthlete()*Athlete::FEMALE :
                $cat = "F";
                break;
            case $this->getRace()->getNumberOfAthlete()*Athlete::MALE :
                $cat = "M";
                break;
            default :
                $cat = "X";
                break;
        }

        return $cat;
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


    /**
     * Set signed
     *
     * @param integer $signed
     *
     * @return Inscription
     */
    public function setSigned($signed)
    {
        $this->signed = $signed;

        return $this;
    }

    /**
     * Get signed
     *
     * @return integer
     */
    public function getSigned()
    {
        return $this->signed;
    }

    /**
     * Set order
     *
     * @param integer $order
     *
     * @return Inscription
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return integer
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set position
     *
     * @param integer $position
     *
     * @return Inscription
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Add option
     *
     * @param \Plopcom\InscriptionsBundle\Entity\InscriptionOption $option
     *
     * @return Inscription
     */
    public function addOption(\Plopcom\InscriptionsBundle\Entity\InscriptionOption $option)
    {
        $this->options[] = $option;

        return $this;
    }

    /**
     * Remove option
     *
     * @param \Plopcom\InscriptionsBundle\Entity\InscriptionOption $option
     */
    public function removeOption(\Plopcom\InscriptionsBundle\Entity\InscriptionOption $option)
    {
        $this->options->removeElement($option);
    }

    /**
     * Get options
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Get total
     *
     * @return float
     */
    public function getTotal()
    {
        $extra = 0;

        foreach ($this->getAthletes() as $athlete){
            foreach ($athlete->getOptions() as $option){
                if (! $option->getRaceOption()->isDocument() && $option->getRaceOption()->getAdditionalFees() && $option->getValue())
                    $extra += $option->getRaceOption()->getAdditionalFees();
            }
        }

        foreach ($this->getOptions() as $option){
            if (! $option->getRaceOption()->isDocument() && $option->getRaceOption()->getAdditionalFees() && $option->getValue())
                $extra += $option->getRaceOption()->getAdditionalFees();
        }

        return $this->getRace()->getEntryFees() + $extra;
    }
}
