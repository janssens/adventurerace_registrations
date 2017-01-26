<?php

namespace Plopcom\InscriptionsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class TypeType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',TextType::class,array('label'=>'Titre','attr' => array('placeholder'=>'Nom du type','class'=>'form-control')))
            ->add('code',TextType::class,array('label'=>'Identifiant','attr' => array('placeholder'=>'nom-du-type','class'=>'form-control',)))
            ->add('description',TextareaType::class,array('label'=>'Description','attr' => array('class'=>'form-control')))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Plopcom\InscriptionsBundle\Entity\Type'
        ));
    }
}
