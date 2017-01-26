<?php
// src/Plopcom/InscriptionsBundle/Entity/AthleteOptionDocument.php
namespace Plopcom\InscriptionsBundle\Entity;

use Plopcom\InscriptionsBundle\Entity\AthleteOption;
use Doctrine\ORM\Mapping as ORM;

/**
 * AthleteOptionDocument
 * @ORM\Entity
 */
class AthleteOptionDocument extends AthleteOption
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