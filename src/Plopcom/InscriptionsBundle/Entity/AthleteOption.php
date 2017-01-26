<?php

namespace Plopcom\InscriptionsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AthleteOption
 *
 * @ORM\Table(name="athlete_option")
 * @ORM\Entity(repositoryClass="Plopcom\InscriptionsBundle\Repository\AthleteOptionRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap( {"string" = "AthleteOptionString", "document" = "AthleteOptionDocument"} )
 */
abstract class AthleteOption
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
     * @ORM\ManyToOne(targetEntity="Athlete", inversedBy="options", cascade={"persist"})
     * @ORM\JoinColumn(name="athlete_id", referencedColumnName="id",onDelete="CASCADE")
     */
    protected $athlete;

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
     * @return AthleteOption
     */
    public function setRaceOption(RaceOption $raceOption = null)
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
     * Set athlete
     *
     * @param \Plopcom\InscriptionsBundle\Entity\Athlete $athlete
     *
     * @return AthleteOption
     */
    public function setAthlete(Athlete $athlete = null)
    {
        $this->athlete = $athlete;

        return $this;
    }

    /**
     * Get athlete
     *
     * @return \Plopcom\InscriptionsBundle\Entity\Athlete
     */
    public function getAthlete()
    {
        return $this->athlete;
    }

}