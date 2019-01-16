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
            ->add('title',TextType::class,array('label'=>'Titre','attr' => array('placeholder'=>'Nom de votre course','class'=>'form-control')))
            ->add('slug',TextType::class,array('label'=>'Identifiant','attr' => array('placeholder'=>'nom-de-votre-course','class'=>'form-control',/*'disabled'=>'true'*/)))
            ->add('type',EntityType::class,array('class'=>'PlopcomInscriptionsBundle:Type','choice_label'=>'title','label'  => 'Type de manifestation','multiple' => false, 'expanded' => true))
            ->add('illustration', DocumentType::class,array('label'=>'illustration (1400x640)'))
            ->add('rules', DocumentType::class,array('label'=>'réglement de l\'épreuve'))
            ->add('date', DateType::class,array(
                'label'=>'Date de l\'épreuve',
                'attr' => array('class'=>'form-control'),
                'years' => range(date('Y'), date('Y')+2)
            ))
            ->add('entry_fees', TextType::class,array('label'=>'Prix','attr' => array('class'=>'form-control')))
            ->add('entry_fees_global', CheckboxType::class,array('label'=>'Prix global','attr' => array('class'=>'form-control'),'required' => false))
            ->add('public', CheckboxType::class,array('label'=>'Visible publiquement','attr' => array('class'=>'form-control'),'required' => false))
            ->add('open', CheckboxType::class,array('label'=>'inscriptions ouvertes','attr' => array('class'=>'form-control'),'required' => false))
            ->add('description',TextareaType::class,array('label'=>'Description','attr' => array('class'=>'form-control'),'required' => true))
            ->add('max_attendee',IntegerType::class,array('label'=>"Nombre d'insription maximum",'attr' => array('class'=>'form-control')))
            ->add('number_of_athlete',IntegerType::class,array('label'=>'Nombre de coureurs par inscription','attr' => array('class'=>'form-control')))
            ->add('max_number_of_athlete',IntegerType::class,array('label'=>'Nombre de coureurs maximum par inscription (à préciser uniquement si le nombre peux varier, sinon laisser vide)','attr' => array('class'=>'form-control')))
            ->add('document_required',CheckboxType::class,array('label'=>'Certificat/Licence requis','attr' => array('class'=>'form-control'),'required' => false))
            ->add('maximal_year',IntegerType::class,array('label'=>'Année de naissance maximale (née en XXXX et avant)','attr' => array('class'=>'form-control'),'required' => false))
            ->add('minimal_year',IntegerType::class,array('label'=>'Année de naissance minimale (née après XXXX)','attr' => array('class'=>'form-control'),'required' => false))
            ->add('distance',IntegerType::class,array('label'=>'Distance à parcourir en metres (1km = 1000m)','attr' => array('class'=>'form-control'),'required' => false))
            ->add('elevation',IntegerType::class,array('label'=>'Gain en dénivelé en metres','attr' => array('class'=>'form-control'),'required' => false))
            ->add('time_duration',IntegerType::class,array('label'=>'Durée en minute','attr' => array('class'=>'form-control'),'required' => false))
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
