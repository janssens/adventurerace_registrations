<?php

namespace Plopcom\InscriptionsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InscriptionOption
 *
 * @ORM\Table(name="inscription_option")
 * @ORM\Entity(repositoryClass="Plopcom\InscriptionsBundle\Repository\InscriptionOptionRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap( {"string" = "InscriptionOptionString", "document" = "InscriptionOptionDocument"} )
 */
abstract class InscriptionOption
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="RaceOption", cascade={"persist"})
     * @ORM\JoinColumn(name="race_option_id", referencedColumnName="id",onDelete="CASCADE")
     */
    protected $race_option;

    /**
     * @ORM\ManyToOne(targetEntity="Inscription",inversedBy="options", cascade={"persist"})
     * @ORM\JoinColumn(name="inscription_id", referencedColumnName="id",onDelete="CASCADE")
     */
    protected $inscription;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set raceOption
     *
     * @param \Plopcom\InscriptionsBundle\Entity\RaceOption $raceOption
     *
     * @return InscriptionOption
     */
    public function setRaceOption(\Plopcom\InscriptionsBundle\Entity\RaceOption $raceOption = null)
    {
        $this->race_option = $raceOption;

        return $this;
    }

    /**
     * Get raceOption
     *
     * @return \Plopcom\InscriptionsBundle\Entity\RaceOption
     */
    public function getRaceOption()
    {
        return $this->race_option;
    }

    /**
     * Set inscription
     *
     * @param \Plopcom\InscriptionsBundle\Entity\Inscription $inscription
     *
     * @return InscriptionOption
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
}

/**
 * AthleteOptionString
 * @ORM\Entity
 */
class InscriptionOptionString extends InscriptionOption
{
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $value;

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * setvalue
     * @param string $value
     * @return null
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}

/**
 * AthleteOptionDocument
 * @ORM\Entity
 */
class InscriptionOptionDocument extends InscriptionOption
{
    /**
     * @ORM\OneToOne(targetEntity="Document", cascade={"persist"})
     * @ORM\JoinColumn(name="document_id", referencedColumnName="id")
     */
    protected $document;


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