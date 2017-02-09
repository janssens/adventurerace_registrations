<?php
// src/Plopcom/InscriptionsBundle/Entity/Race.php

namespace Plopcom\InscriptionsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="race")
 * @UniqueEntity("slug")
 */
class Race
{

    const PAYPAL_URL = 'https://www.paypal.com/cgi-bin/webscr';
    //const PAYPAL_URL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    protected $title;

    /**
     * @ORM\Column(type="string", unique=true)
     * @Assert\NotBlank()
     */
    protected $slug;

    /**
     * @ORM\OneToOne(targetEntity="Address")
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id")
     */
    protected $address;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $date;

    /**
     * @ORM\Column(type="text")
     */
    protected $description;

    /**
     * @ORM\Column(type="integer")
     */
    protected $max_attendee;

    /**
     * @ORM\Column(type="float",nullable=true)
     */
    protected $entry_fees;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $open = false;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $public = false;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $document_required = true;

    /**
     * @ORM\Column(type="integer")
     */
    protected $number_of_athlete;

    /**
     * @ORM\Column(type="integer")
     */
    protected $distance;

    /**
     * @ORM\Column(type="integer")
     */
    protected $elevation;

    /**
     * @ORM\OneToOne(targetEntity="Document", cascade={"persist"})
     * @ORM\JoinColumn(name="rules_id", referencedColumnName="id",onDelete="CASCADE",nullable=true)
     */
    protected $rules;
    
    /**
     * @ORM\OneToOne(targetEntity="Document", cascade={"persist"})
     * @ORM\JoinColumn(name="illustration_id", referencedColumnName="id",onDelete="CASCADE",nullable=true)
     */
    protected $illustration;

    /**
     * @ORM\ManyToOne(targetEntity="Type",inversedBy="races")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     */
    protected $type;

    /**
     * @ORM\OneToMany(targetEntity="Inscription", mappedBy="race", cascade={"persist", "remove", "merge"})
     * @ORM\OrderBy({"position" = "ASC", "id" = "ASC"})
     */
    protected $inscriptions;

    /**
     * @ORM\ManyToMany(targetEntity="RaceOption", mappedBy="races", cascade={"persist", "remove", "merge"})
     */
    protected $options;

    /**
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="races", cascade={"persist", "merge"})
     * @ORM\JoinColumns({
     *  @ORM\JoinColumn(name="event_id", referencedColumnName="id",onDelete="CASCADE")
     * })
     */
    protected $event;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->inscriptions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->event = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function resume(){
        return ($this->getDistance()/1000)."km & ".$this->getElevation()."m d+";
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
     * @return Race
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
     * Set slug
     *
     * @param string $slug
     *
     * @return Race
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Race
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Race
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
     * Set numberOfAthlete
     *
     * @param integer $numberOfAthlete
     *
     * @return Race
     */
    public function setNumberOfAthlete($numberOfAthlete)
    {
        $this->number_of_athlete = $numberOfAthlete;

        return $this;
    }

    /**
     * Get numberOfAthlete
     *
     * @return integer
     */
    public function getNumberOfAthlete()
    {
        return $this->number_of_athlete;
    }

    /**
     * Set distance
     *
     * @param integer $distance
     *
     * @return Race
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;

        return $this;
    }

    /**
     * Get distance
     *
     * @return integer
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * Set elevation
     *
     * @param integer $elevation
     *
     * @return Race
     */
    public function setElevation($elevation)
    {
        $this->elevation = $elevation;

        return $this;
    }

    /**
     * Get elevation
     *
     * @return integer
     */
    public function getElevation()
    {
        return $this->elevation;
    }

    /**
     * Set address
     *
     * @param \Plopcom\InscriptionsBundle\Entity\Address $address
     *
     * @return Race
     */
    public function setAddress(\Plopcom\InscriptionsBundle\Entity\Address $address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return \Plopcom\InscriptionsBundle\Entity\Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set type
     *
     * @param \Plopcom\InscriptionsBundle\Entity\Type $type
     *
     * @return Race
     */
    public function setType(\Plopcom\InscriptionsBundle\Entity\Type $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \Plopcom\InscriptionsBundle\Entity\Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Add inscription
     *
     * @param \Plopcom\InscriptionsBundle\Entity\Inscription $inscription
     *
     * @return Race
     */
    public function addInscription(\Plopcom\InscriptionsBundle\Entity\Inscription $inscription)
    {
        $this->inscriptions[] = $inscription;

        return $this;
    }

    /**
     * Remove inscription
     *
     * @param \Plopcom\InscriptionsBundle\Entity\Inscription $inscription
     */
    public function removeInscription(\Plopcom\InscriptionsBundle\Entity\Inscription $inscription)
    {
        $this->inscriptions->removeElement($inscription);
    }

    /**
     * Get inscriptions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInscriptions()
    {
        return $this->inscriptions;
    }

    /**
     * Add event
     *
     * @param \Plopcom\InscriptionsBundle\Entity\Event $event
     *
     * @return Race
     */
    public function addEvent(\Plopcom\InscriptionsBundle\Entity\Event $event)
    {
        $this->event[] = $event;

        return $this;
    }

    /**
     * Remove event
     *
     * @param \Plopcom\InscriptionsBundle\Entity\Event $event
     */
    public function removeEvent(\Plopcom\InscriptionsBundle\Entity\Event $event)
    {
        $this->event->removeElement($event);
    }

    /**
     * Get event
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set event
     *
     * @param \Plopcom\InscriptionsBundle\Entity\Event $event
     *
     * @return Race
     */
    public function setEvent(\Plopcom\InscriptionsBundle\Entity\Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Set illustration
     *
     * @param \Plopcom\InscriptionsBundle\Entity\Document $illustration
     *
     * @return Race
     */
    public function setIllustration(\Plopcom\InscriptionsBundle\Entity\Document $illustration = null)
    {
        $this->illustration = $illustration;

        return $this;
    }

    /**
     * Get illustration
     *
     * @return \Plopcom\InscriptionsBundle\Entity\Document
     */
    public function getIllustration()
    {
        return $this->illustration;
    }

    /**
     * Set paypalButtonUrl
     *
     * @param string $paypalButtonUrl
     *
     * @return Race
     */
    public function setPaypalButtonUrl($paypalButtonUrl)
    {
        $this->paypal_button_url = $paypalButtonUrl;

        return $this;
    }

    /**
     * Get paypalButtonUrl
     *
     * @return string
     */
    public function getPaypalButtonUrl()
    {
        return $this->paypal_button_url;
    }

    /**
     * Set maxAttendee
     *
     * @param integer $maxAttendee
     *
     * @return Race
     */
    public function setMaxAttendee($maxAttendee)
    {
        $this->max_attendee = $maxAttendee;

        return $this;
    }

    /**
     * Get maxAttendee
     *
     * @return integer
     */
    public function getMaxAttendee()
    {
        return $this->max_attendee;
    }

    /**
     * Set entryFees
     *
     * @param float $entryFees
     *
     * @return Race
     */
    public function setEntryFees($entryFees)
    {
        $this->entry_fees = $entryFees;

        return $this;
    }

    /**
     * Get entryFees
     *
     * @return float
     */
    public function getEntryFees()
    {
        return $this->entry_fees;
    }

    /**
     * Set open
     *
     * @param boolean $open
     *
     * @return Race
     */
    public function setOpen($open)
    {
        $this->open = $open;

        return $this;
    }

    /**
     * Get open
     *
     * @return boolean
     */
    public function getOpen()
    {
        return $this->open;
    }

    /**
     * Set public
     *
     * @param boolean $public
     *
     * @return Race
     */
    public function setPublic($public)
    {
        $this->public = $public;

        return $this;
    }

    /**
     * Get public
     *
     * @return boolean
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * is past
     *
     * @return boolean
     */
    public function isPast()
    {
        $now = new DateTime();
        return ($now > $this->getDate());
    }

    /**
     * Set documentRequired
     *
     * @param boolean $documentRequired
     *
     * @return Race
     */
    public function setDocumentRequired($documentRequired)
    {
        $this->document_required = $documentRequired;

        return $this;
    }

    /**
     * Get documentRequired
     *
     * @return boolean
     */
    public function getDocumentRequired()
    {
        return $this->document_required;
    }

    /**
     * Add option
     *
     * @param \Plopcom\InscriptionsBundle\Entity\RaceOption $option
     *
     * @return Race
     */
    public function addOption(\Plopcom\InscriptionsBundle\Entity\RaceOption $option)
    {
        $this->options[] = $option;

        return $this;
    }

    /**
     * Remove option
     *
     * @param \Plopcom\InscriptionsBundle\Entity\RaceOption $option
     */
    public function removeOption(\Plopcom\InscriptionsBundle\Entity\RaceOption $option)
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
     * Set rules
     *
     * @param \Plopcom\InscriptionsBundle\Entity\Document $rules
     *
     * @return Race
     */
    public function setRules(\Plopcom\InscriptionsBundle\Entity\Document $rules = null)
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * Get rules
     *
     * @return \Plopcom\InscriptionsBundle\Entity\Document
     */
    public function getRules()
    {
        return $this->rules;
    }
}
