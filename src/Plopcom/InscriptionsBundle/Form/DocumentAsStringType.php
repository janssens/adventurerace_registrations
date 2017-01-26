<?php
// src/Plopcom/InscriptionsBundle/Form/DocumentAsStringType.php

namespace Plopcom\InscriptionsBundle\Form;

use Doctrine\Common\Persistence\ObjectManager;
use Plopcom\InscriptionsBundle\Form\DataTransformer\DocumentToNumberTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Plopcom\InscriptionsBundle\Form\DocumentType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentAsStringType extends AbstractType
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new DocumentToNumberTransformer($this->manager);
        $builder->addModelTransformer($transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'invalid_message' => 'Le document souhaité n\'existe pas',
        ));
    }

    public function getParent()
    {
        return DocumentType::class;
    }
}
