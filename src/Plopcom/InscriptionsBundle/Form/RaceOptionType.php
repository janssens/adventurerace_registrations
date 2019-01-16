<?php

namespace Plopcom\InscriptionsBundle\Form;

use Plopcom\InscriptionsBundle\Entity\RaceOption;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Plopcom\InscriptionsBundle\Form\DocumentType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer;

class RaceOptionType extends AbstractType
{
    protected $_types;
    protected $_targets;

    public function __construct(){
        $types = array();
        $types[] = array('id'=>RaceOption::TYPE_RADIO,'name'=>'radio','hint'=>'Boutons radio');
        $types[] = array('id'=>RaceOption::TYPE_CHECKBOX,'name'=>'checkbox','hint'=>'Une case à cocher');
        $types[] = array('id'=>RaceOption::TYPE_CHECKBOX_READ,'name'=>'click and check','hint'=>'Une case à cocher après avoir visité un document');
        $types[] = array('id'=>RaceOption::TYPE_SELECT,'name'=>'select','hint'=>'Menu déroulant');
        $types[] = array('id'=>RaceOption::TYPE_MULTISELECT,'name'=>'multiselect','hint'=>'Menu déroulant choix multiple');
        $types[] = array('id'=>RaceOption::TYPE_INT,'name'=>'integer','hint'=>'Entier');
        $types[] = array('id'=>RaceOption::TYPE_TEXT,'name'=>'text','hint'=>'Texte');
        $types[] = array('id'=>RaceOption::TYPE_TEXTAREA,'name'=>'textarea','hint'=>'Zone de Texte');
        $types[] = array('id'=>RaceOption::TYPE_EMAIL,'name'=>'email','hint'=>'Email');
        $types[] = array('id'=>RaceOption::TYPE_DOCUMENT,'name'=>'document','hint'=>'Fichier join');
        $this->_types = $types;

        $targets = array();
        $targets[] = array('id'=>RaceOption::TARGET_ATHLETE,'name'=>'athlete','hint'=>'Option pour les athletes');
        $targets[] = array('id'=>RaceOption::TARGET_INSCRIPTION,'name'=>'inscription','hint'=>'Option pour l\'équipe');
        $this->_targets = $targets;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, array(
                'choices' => $this->getChoicesForType(),
                'attr' => array('class'=>'form-control'),
                'multiple' => false))
            ->add('target', ChoiceType::class, array(
                'choices' => $this->getChoicesForTarget(),
                'attr' => array('class'=>'form-control'),
                'multiple' => false))
            ->add('choices', TextType::class, ['required' => false,'attr' => array('class'=>'form-control'),])
            ->add('placeholder', TextType::class, array('attr' => array('class'=>'form-control'),'required'=>false))
            ->add('additional_fees', TextType::class,array('label'=>'Coût supplémentaire (si choisi)','attr' => array('class'=>'form-control'),'required'=>false))
            ->add('upper_limit_fees', TextType::class,array('label'=>'Coût plafond (si choisi)','attr' => array('class'=>'form-control'),'required'=>false))
            ->add('document', DocumentType::class,array('label'=>'Document joint','required'=>false))
            ->add('title', TextType::class, array('attr' => array('class'=>'form-control'),))
            ->add('required')
            ->add('races', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', [
                'class' => 'Plopcom\InscriptionsBundle\Entity\Race',
                'choice_label' => function ($value, $key, $index) {
                    return $value->getEvent()->getTitle()." > ".$value->getDate()->format('Y-m-d')." > ".$value->getTitle();
                },
                'multiple' => true,
            ])
        ;

        $builder->get('choices')
            ->addModelTransformer(new CallbackTransformer(
                function ($choicesAsArray) {
                    // transform the array to a string
                    return implode(', ', $choicesAsArray);
                },
                function ($choicesAsString) {
                    // transform the string back to an array
                    return explode(', ', $choicesAsString);
                }
            ))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Plopcom\InscriptionsBundle\Entity\RaceOption'
        ));
    }

    private function getChoicesForType(){
        $choices = array();
        foreach ($this->_types as $type)
            $choices[$type['hint']] = $type['id'];
        return $choices;
    }

    private function getChoicesForTarget(){
        $choices = array();
        foreach ($this->_targets as $target)
            $choices[$target['hint']] = $target['id'];
        return $choices;
    }

}
