<?php

namespace Plopcom\InscriptionsBundle\Form;

use Proxies\__CG__\Plopcom\InscriptionsBundle\Entity\Document;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RaceType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',TextType::class,array('label'=>'titre','attr' => array('placeholder'=>'Nom de votre course','class'=>'form-control')))
            ->add('slug',TextType::class,array('label'=>'identifiant','attr' => array('placeholder'=>'nom-de-votre-course','class'=>'form-control',/*'disabled'=>'true'*/)))
            ->add('type',EntityType::class,array('class'=>'PlopcomInscriptionsBundle:Type','choice_label'=>'title','label'  => 'Type de manifestation','multiple' => false, 'expanded' => true))
            ->add('illustration', DocumentType::class)
            ->add('date', DateType::class,array('attr' => array('class'=>'form-control')))
            ->add('entry_fees', TextType::class,array('label'=>'Prix','attr' => array('class'=>'form-control')))
            ->add('public', CheckboxType::class,array('label'=>'Visible publiquement','attr' => array('class'=>'form-control'),'required' => false))
            ->add('open', CheckboxType::class,array('label'=>'Ouvert','attr' => array('class'=>'form-control'),'required' => false))
            ->add('description',TextareaType::class,array('label'=>'description','attr' => array('class'=>'form-control')))
            ->add('max_attendee',IntegerType::class,array('label'=>"Nombre d'insription maximum",'attr' => array('class'=>'form-control')))
            ->add('number_of_athlete',IntegerType::class,array('label'=>'Nombre de coureurs par inscription','attr' => array('class'=>'form-control')))
            ->add('distance',IntegerType::class,array('label'=>'Distance à parcourir en metres','required'=>false,'attr' => array('class'=>'form-control')))
            ->add('elevation',IntegerType::class,array('label'=>'Gain en dénivelé en metres','required'=>false,'attr' => array('class'=>'form-control')))
            ->add('paypal_hosted_button_id',TextType::class,array('label'=>'Id du bouton paypal','required'=>false,'attr' => array('class'=>'form-control')))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Plopcom\InscriptionsBundle\Entity\Race'
        ));
    }
}
