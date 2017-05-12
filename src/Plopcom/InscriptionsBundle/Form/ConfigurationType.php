<?php

namespace Plopcom\InscriptionsBundle\Form;

use Proxies\__CG__\Plopcom\InscriptionsBundle\Entity\Document;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ConfigurationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('page_title',TextType::class,array('label'=>'Titre','attr' => array('placeholder'=>'Nom du site','class'=>'form-control')))
            ->add('meta_title',TextType::class,array('label'=>'meta title','attr' => array('placeholder'=>'Nom du site','class'=>'form-control')))
            ->add('meta_keywords',TextType::class,array('label'=>'meta keywords','attr' => array('placeholder'=>'Nom du site','class'=>'form-control')))
            ->add('meta_description',TextareaType::class,array('label'=>'meta description','attr' => array('class'=>'form-control')))
            ->add('contact_email',EmailType::class,array('label'=>'contact email','attr' => array('placeholder'=>'contact@plopcom.fr','class'=>'form-control')))
            ->add('contact_name',TextType::class,array('label'=>'contact name','attr' => array('placeholder'=>'Gaëtan Janssens','class'=>'form-control')));
        $builder
            ->add('headerImageFile', VichImageType::class, array(
                'required'      => false,
                'allow_delete'  => true, // not mandatory, default is true
                'download_link' => true, // not mandatory, default is true
            ));
        $builder
            ->add('content',TextareaType::class,array('label'=>'texte d\'accueil','attr' => array('class'=>'form-control'),'required' => false))
            ->add('message',TextareaType::class,array('label'=>'Message important','attr' => array('class'=>'form-control'),'required' => false));
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Plopcom\InscriptionsBundle\Entity\Configuration'
        ));
    }
}
