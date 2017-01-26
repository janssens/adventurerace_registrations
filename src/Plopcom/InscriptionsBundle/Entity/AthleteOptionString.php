<?php
// src/Plopcom/InscriptionsBundle/Entity/AthleteOptionString.php
namespace Plopcom\InscriptionsBundle\Entity;

use Plopcom\InscriptionsBundle\Entity\AthleteOption;
use Doctrine\ORM\Mapping as ORM;

/**
* AthleteOptionString
* @ORM\Entity
*/
class AthleteOptionString extends AthleteOption
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