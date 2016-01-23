<?php

namespace Plopcom\InscriptionsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class AthleteType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lastname',TextType::class,array('label'=>'Nom de famille','attr' => array('placeholder' => 'nom de famille','class'=>'form-control')))
            ->add('firstname',TextType::class,array('label'=>'Prénom','attr' => array('placeholder' => 'prénom','class'=>'form-control')))
            ->add('email', EmailType::class, array('label' => 'Email','attr' => array('placeholder' => 'email@valide.fr','class'=>'form-control')))
            ->add('gender', ChoiceType::class, array(
                'label' => "Sexe",
                'choices' => array('Femme' => 2,'Homme' => 1 ),
                'expanded' => true,
                'multiple' => false,
                //'choices_as_values' => true,
            ))
            ->add('dob', BirthdayType::class,array('label'=>'Date de naissance','attr' => array('class'=>'form-control')))
        ;

        $builder->add('address', AddressType::class,array('label'=>' '));

        $builder
            ->add('document',DocumentType::class,array('label'=>'Licence / certificat medical'))
            ->add('phone',TextType::class,array('label'=>'Téléphone','attr' => array('placeholder' => '0102030405','class'=>'form-control')))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Plopcom\InscriptionsBundle\Entity\Athlete'
        ));
    }
}
