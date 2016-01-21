<?php
// src/Plopcom/InscriptionsBundle/Entity/Athlete.php

namespace Plopcom\InscriptionsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="athlete")
 */
class Athlete
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
    protected $lastname;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    protected $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    protected $email;

    /**
     * @ORM\OneToOne(targetEntity="Document", cascade={"persist"})
     * @ORM\JoinColumn(name="document_id", referencedColumnName="id")
     */
    protected $document;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $dob;

    /**
     * @ORM\Column(type="string")
     */
    protected $phone;

    /**
     * @ORM\ManyToOne(targetEntity="Inscription", inversedBy="athletes")
     * @ORM\JoinColumn(name="inscription_id", referencedColumnName="id",onDelete="CASCADE")
     */
    protected $inscription;

    /**
     * @ORM\OneToOne(targetEntity="Address", cascade={"persist"})
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id",onDelete="CASCADE")
     */
    protected $address;


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
     * Set lastname
     *
     * @param string $lastname
     *
     * @return Athlete
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     *
     * @return Athlete
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    public function getFullName(){
        return strtolower($this->getFirstname()).' '.strtoupper($this->getLastname());
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Athlete
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
     * Set file
     *
     * @param string $file
     *
     * @return Athlete
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get file
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set dob
     *
     * @param \DateTime $dob
     *
     * @return Athlete
     */
    public function setDob($dob)
    {
        $this->dob = $dob;

        return $this;
    }

    /**
     * Get dob
     *
     * @return \DateTime
     */
    public function getDob()
    {
        return $this->dob;
    }

    /**
     * Set inscription
     *
     * @param \Plopcom\InscriptionsBundle\Entity\Inscription $inscription
     *
     * @return Athlete
     */
    public function setInscription(\Plopcom\InscriptionsBundle\Entity\Inscription $inscription = null)
    {
        $this->inscription = $inscription;

        return $this;
    }

    /**
     * Get inscription
     *
     * @return \Plopcom\InscriptionsBundle\Entity\Inscription
     */
    public function getInscription()
    {
        return $this->inscription;
    }

    /**
     * Set address
     *
     * @param \Plopcom\InscriptionsBundle\Entity\Address $address
     *
     * @return Athlete
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
     * Set phone
     *
     * @param string $phone
     *
     * @return Athlete
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

    public function __toString(){
        return '#' . $this->getId() . ' ' . $this->getFirstName() . ' ' . $this->getLastName() . '(' . $this->getEmail() . ')';
    }

    /**
     * Set document
     *
     * @param \Plopcom\InscriptionsBundle\Entity\Document $document
     *
     * @return Athlete
     */
    public function setDocument(\Plopcom\InscriptionsBundle\Entity\Document $document = null)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get document
     *
     * @return \Plopcom\InscriptionsBundle\Entity\Document
     */
    public function getDocument()
    {
        return $this->document;
    }
}
