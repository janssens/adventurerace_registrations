<?php

namespace Plopcom\InscriptionsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',TextType::class,array('label'=>'titre','attr' => array('placeholder'=>'Nom de votre événement','class'=>'form-control')))
            ->add('slug',TextType::class,array('label'=>'identifiant','attr' => array('placeholder'=>'nom-de-votre-evenement','class'=>'form-control',/*'disabled'=>'true'*/)))
            ->add('email', EmailType::class, array('label' => 'Email de contact','attr' => array('placeholder' => 'email@valide.fr','class'=>'form-control')))
            ->add('paypal_account_email', EmailType::class, array('label' => 'Email du compte paypal','attr' => array('placeholder' => 'email@valide.fr','class'=>'form-control'),'required'=>false))
            ->add('description',TextareaType::class,array('attr' => array('class'=>'form-control')))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Plopcom\InscriptionsBundle\Entity\Event'
        ));
    }
}
