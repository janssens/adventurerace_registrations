<?php

namespace Plopcom\InscriptionsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Plopcom\InscriptionsBundle\Form\DataTransformer\DocumentToNumberTransformer;
use Plopcom\InscriptionsBundle\Form\DocumentAsStringType;
use Plopcom\InscriptionsBundle\Form\DocumentType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * RaceOption
 *
 * @ORM\Table(name="race_option")
 * @ORM\Entity(repositoryClass="Plopcom\InscriptionsBundle\Repository\RaceOptionRepository")
 */
class RaceOption
{
    const TARGET_ATHLETE = 1;
    const TARGET_INSCRIPTION = 2;

    const TYPE_RADIO = 1;
    const TYPE_CHECKBOX = 2;
    const TYPE_SELECT = 3;
    const TYPE_MULTISELECT = 4;
    const TYPE_INT = 5;
    const TYPE_TEXT = 6;
    const TYPE_TEXTAREA = 7;
    const TYPE_EMAIL = 8;
    const TYPE_DOCUMENT = 9;

    const TYPE_CHECKBOX_READ = 10;

        /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="type", type="integer")
     */
    private $type;

    /**
     * @var int
     *
     * @ORM\Column(name="target", type="integer")
     */
    private $target;

    /**
     * @var array
     *
     * @ORM\Column(name="choices", type="array", nullable=true)
     */
    private $choices;

    /**
     * @var string
     *
     * @ORM\Column(name="placeholder", type="string", length=255, nullable=true)
     */
    private $placeholder;

    /**
     * @ORM\Column(type="float",nullable=true)
     */
    protected $additional_fees;

    /**
     * @ORM\Column(type="float",nullable=true)
     */
    protected $upper_limit_fees;
    
    /**
     * @ORM\OneToOne(targetEntity="Document", cascade={"persist"})
     * @ORM\JoinColumn(name="document_id", referencedColumnName="id")
     */
    protected $document;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $required = false;

    /**
     * @ORM\ManyToMany(targetEntity="Race",inversedBy="options", cascade={"persist","remove"})
     */
    protected $races;
    
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
     * Set type
     *
     * @param integer $type
     *
     * @return RaceOption
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get type
     *
     * @return int
     */
    public function getTypeAsText()
    {
        $return = null;
        switch ($this->getType()){
            case RaceOption::TYPE_RADIO:
                $return = 'TYPE_RADIO';
                break;
            case RaceOption::TYPE_SELECT:
                $return = 'TYPE_SELECT';
                break;
            case RaceOption::TYPE_MULTISELECT:
                $return = 'TYPE_MULTISELECT';
                break;
            case RaceOption::TYPE_CHECKBOX:
                $return = 'TYPE_CHECKBOX';
                break;
            case RaceOption::TYPE_INT:
                $return = 'TYPE_INT';
                break;
            case RaceOption::TYPE_TEXT:
                $return = 'TYPE_TEXT';
                break;
            case RaceOption::TYPE_TEXTAREA:
                $return = 'TYPE_TEXTAREA';
                break;
            case RaceOption::TYPE_EMAIL:
                $return = 'TYPE_EMAIL';
                break;
            case RaceOption::TYPE_DOCUMENT:
                $return = 'TYPE_DOCUMENT';
                break;
            case RaceOption::TYPE_CHECKBOX_READ:
                $return = 'TYPE_CHECKBOX_READ';
                break;
            default:
                $return = 'N/A';
                break;
        }
        return $return;
    }

    /**
     * Set choices
     *
     * @param array $choices
     *
     * @return RaceOption
     */
    public function setChoices($choices)
    {
        $this->choices = $choices;

        return $this;
    }

    /**
     * Get choices
     *
     * @return array
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * Set placeholder
     *
     * @param string $placeholder
     *
     * @return RaceOption
     */
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * Get placeholder
     *
     * @return string
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return RaceOption
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
     * @return RaceOption
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
     * Set target
     *
     * @param integer $target
     *
     * @return RaceOption
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Get target
     *
     * @return integer
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Is For Athlete
     * @return boolean
     *
     */
    public function isForAthlete()
    {
        return $this->getTarget() == self::TARGET_ATHLETE;
    }

    /**
     * Get Form
     * @return string
     */
    public function getForm()
    {
        $defaultData = array();
        $form = $this->createFormBuilder($defaultData)
            ->add('field', TextType::class)
            ->getForm();
        return $form;
    }

    /**
     * Set required
     *
     * @param boolean $required
     *
     * @return RaceOption
     */
    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * Get required
     *
     * @return boolean
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Get Type
     *
     * @return integer
     */
    public function getTypeClass()
    {
        $return = null;
        switch ($this->getType()){
            case RaceOption::TYPE_RADIO:
            case RaceOption::TYPE_SELECT:
            case RaceOption::TYPE_MULTISELECT:
                $return = ChoiceType::class;
                break;
            case RaceOption::TYPE_CHECKBOX:
            case RaceOption::TYPE_CHECKBOX_READ:
                $return = CheckboxType::class;
                break;
            case RaceOption::TYPE_INT:
                $return = IntegerType::class;
                break;
            case RaceOption::TYPE_TEXT:
                $return = TextType::class;
                break;
            case RaceOption::TYPE_TEXTAREA:
                $return = TextareaType::class;
                break;
            case RaceOption::TYPE_EMAIL:
                $return = EmailType::class;
                break;
            case RaceOption::TYPE_DOCUMENT:
                $return = DocumentType::class;
                break;
            default:
                $return = TextType::class;
                break;
        }
        return $return;
    }

    /**
     * IsDocument
     * return true if Type == Type_Doc
     * @return boolean
     */
    public function isDocument(){
        return $this->getType()==RaceOption::TYPE_DOCUMENT;
    }

    /**
     * IsCheckbox
     * return true if Type == TYPE_CHECKBOX
     * @return boolean
     */
    public function isCheckbox(){
        return $this->getType()==RaceOption::TYPE_CHECKBOX OR $this->getType()==RaceOption::TYPE_CHECKBOX_READ;
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
     * @return RaceOption
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
     * Set document
     *
     * @param \Plopcom\InscriptionsBundle\Entity\Document $document
     *
     * @return RaceOption
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

    /**
     * Set additionalFees
     *
     * @param float $additionalFees
     *
     * @return RaceOption
     */
    public function setAdditionalFees($additionalFees)
    {
        $this->additional_fees = $additionalFees;

        return $this;
    }

    /**
     * Get additionalFees
     *
     * @return float
     */
    public function getAdditionalFees()
    {
        return $this->additional_fees;
    }

    /**
     * Set upperLimitFees
     *
     * @param float $upper_limit_fees
     *
     * @return RaceOption
     */
    public function setUpperLimitFees($upper_limit_fees)
    {
        $this->upper_limit_fees = $upper_limit_fees;

        return $this;
    }

    /**
     * Get upperLimitFees
     *
     * @return float
     */
    public function getUpperLimitFees()
    {
        return $this->upper_limit_fees;
    }

}
