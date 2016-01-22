<?php

namespace Plopcom\InscriptionsBundle\Form;

use Plopcom\InscriptionsBundle\Entity\Inscription;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\ChoiceList;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class InscriptionType extends AbstractType
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title',TextType::class,array('label'=>'Club / Team','attr' => array('placeholder'=>'club','class'=>'form-control'),'required'=>false));

        $user = $this->tokenStorage->getToken()->getUser();

        if ($user && is_object($user) && $user->hasRole('ROLE_ADMIN')) {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($user) {
                $form = $event->getForm();

                $form->add('status', ChoiceType::class, array(
                    'choices' => array(
                            'Valide' => Inscription::STATUS_VALID,
                            'Non vérifié'=> Inscription::STATUS_UNCHECKED,
                            'Non valide'=> Inscription::STATUS_UNVALID,
                    ), 'label' => "Status de l'inscription",'attr' => array('class'=>'form-control')));
                $form->add('payement_status', ChoiceType::class, array(
                    'choices' => array(
                        'Echoué' => Inscription::PAYEMENT_STATUS_FAILED,
                        'Non payé'=> Inscription::PAYEMENT_STATUS_NOT_PAYED,
                        'Payé'=> Inscription::PAYEMENT_STATUS_PAYED,
                        'En attente retour'=> Inscription::PAYEMENT_STATUS_WAITING,
                    ), 'label' => "Status du payement",'attr' => array('class'=>'form-control')));
                $form->add('admin_comment', TextType::class, array( 'label' => "Commentaire admin",'attr' => array('class'=>'form-control'),'required'=> false));
            });
        }

        $builder->add('athletes', CollectionType::class, array(
            'entry_type' => AthleteType::class
        ));

        $builder->add('save',SubmitType::class,array('label'=>'Enregistrer', 'attr'=>array('class'=>'btn btn-primary')))
            ->add('reset',ResetType::class,array('label'=>'Vider', 'attr'=>array('class'=>'btn btn-warning')));
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Plopcom\InscriptionsBundle\Entity\Inscription'
        ));
    }

}
