<?php
// src/Plopcom/InscriptionsBundle/Entity/Type.php

namespace Plopcom\InscriptionsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="type")
 */
class Type
{
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
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    protected $code;

    /**
     * @ORM\Column(type="text")
     */
    protected $description;

    /**
     * @ORM\OneToMany(targetEntity="Race", mappedBy="type")
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
     * @return Type
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
     * Set code
     *
     * @param string $code
     *
     * @return Type
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Type
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
     * Constructor
     */
    public function __construct()
    {
        $this->races = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set races
     *
     * @param \Plopcom\InscriptionsBundle\Entity\Race $races
     *
     * @return Type
     */
    public function setRaces(\Plopcom\InscriptionsBundle\Entity\Race $races = null)
    {
        $this->races = $races;

        return $this;
    }

    /**
     * Get races
     *
     * @return \Plopcom\InscriptionsBundle\Entity\Race
     */
    public function getRaces()
    {
        return $this->races;
    }

    /**
     * Add race
     *
     * @param \Plopcom\InscriptionsBundle\Entity\Race $race
     *
     * @return Type
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
}
