<?php

namespace Plopcom\InscriptionsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('line_1',TextType::class,array('label'=>'Adresse','attr' => array('placeholder' => 'Rue','class'=>'form-control')))
            ->add('line_2',TextType::class,array('label'=>'Adresse ligne 2','required'=>false,'attr' => array('placeholder' => 'Batiment','class'=>'form-control')))
//            ->add('line_3',TextType::class,array('label'=>'Adresse ligne 3','required'=>false,'attr' => array('placeholder' => '','class'=>'form-control')))
            ->add('city',TextType::class,array('label'=>'Ville','attr' => array('placeholder' => 'Ville','class'=>'form-control')))
//            ->add('county_province')
            ->add('zip_or_postcode',TextType::class,array('label'=>'Code Postal','attr' => array('placeholder' => '00000','class'=>'form-control')))
            ->add('country',TextType::class,array('label'=>'Pays','attr' => array('placeholder' => 'pays','class'=>'form-control')))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Plopcom\InscriptionsBundle\Entity\Address'
        ));
    }
}
