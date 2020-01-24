<?php
// src/Plopcom/InscriptionsBundle/Entity/Event.php

namespace Plopcom\InscriptionsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="event")
 * @UniqueEntity("slug")
 */
class Event
{
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
     * @ORM\Column(type="string", unique=true)
     */
    protected $slug;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    protected $description;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email()
     * @Assert\NotBlank()
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Email()
     */
    protected $paypal_account_email;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="events", cascade={"persist", "merge"})
     * @ORM\JoinColumns({
     *  @ORM\JoinColumn(name="owner_id", referencedColumnName="id",onDelete="CASCADE")
     * })
     */
    protected $owner;

    /**
     * @ORM\OneToMany(targetEntity="Race", mappedBy="event")
     */
    protected $races;

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
     * @return Event
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
     * @return \DateTime|null
     */
    public function getDate()
    {
        $date = null;
        foreach ($this->getRaces() as $race){
            if (!$date or $race->getDate()<$date){
                $date = $race->getDate();
            }
        }
        return $date;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Event
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
     * Add owner
     *
     * @param \Plopcom\InscriptionsBundle\Entity\User $owner
     *
     * @return Event
     */
    public function addOwner(\Plopcom\InscriptionsBundle\Entity\User $owner)
    {
        $this->owner[] = $owner;

        return $this;
    }

    /**
     * Remove owner
     *
     * @param \Plopcom\InscriptionsBundle\Entity\User $owner
     */
    public function removeOwner(\Plopcom\InscriptionsBundle\Entity\User $owner)
    {
        $this->owner->removeElement($owner);
    }

    /**
     * Get owner
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set owner
     *
     * @param \Plopcom\InscriptionsBundle\Entity\User $owner
     *
     * @return Event
     */
    public function setOwner(\Plopcom\InscriptionsBundle\Entity\User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->races = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add race
     *
     * @param \Plopcom\InscriptionsBundle\Entity\Race $race
     *
     * @return Event
     */
    public function addRace(\Plopcom\InscriptionsBundle\Entity\Race $race)
    {
        $this->races[] = $race;

        return $this;
    }

    /**
     * Remove race
     *
     * @param \Plopcom\InscriptionsBundle\Entity\Race $race
     */
    public function removeRace(\Plopcom\InscriptionsBundle\Entity\Race $race)
    {
        $this->races->removeElement($race);
    }

    /**
     * Get races
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRaces()
    {
        return $this->races;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Event
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
     * Set paypalAccountEmail
     *
     * @param string $paypalAccountEmail
     *
     * @return Event
     */
    public function setPaypalAccountEmail($paypalAccountEmail)
    {
        $this->paypal_account_email = $paypalAccountEmail;

        return $this;
    }

    /**
     * Get paypalAccountEmail
     *
     * @return string
     */
    public function getPaypalAccountEmail()
    {
        return $this->paypal_account_email;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Event
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
     * get Tyoes
     *
     * @return array of Type
     */
    public function getTypes()
    {
        $return = array();
        foreach ($this->getRaces() as $race){
            $return[$race->getType()->getId()] = $race->getType();
        }
        return $return;
    }

    public function getOpenRaces()
    {
        $return = array();
        foreach ($this->getRaces() as $race){
            if ($race->getOpen() && $race->getPublic() && !$race->isPast())
                $return[$race->getId()] = $race;
        }
        return $return;
    }

    public function getFutureRaces()
    {
        $return = array();
        foreach ($this->getRaces() as $race){
            if ($race->getPublic() && !$race->isPast())
                $return[$race->getId()] = $race;
        }
        return $return;
    }
}
