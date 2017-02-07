<?php

namespace Plopcom\InscriptionsBundle\Form;

use Plopcom\InscriptionsBundle\Form\DataTransformer\DocumentToNumberTransformer;
use Plopcom\InscriptionsBundle\Entity\RaceOption;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\Common\Persistence\ObjectManager;

class AthleteOptionType extends AbstractType
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            $raceOption = $data->getRaceOption();

            $attr = array(
                'label' => $raceOption->getTitle(),
                'attr' => array(
                    'placeholder' =>  $raceOption->getPlaceholder(),
                    'class' => 'form-control',
                ),
                'required' => $raceOption->getRequired()
            );

            if (($raceOption->getType()==RaceOption::TYPE_SELECT)
                ||($raceOption->getType()==RaceOption::TYPE_MULTISELECT)
                ||($raceOption->getType()==RaceOption::TYPE_RADIO)
            )
            {
                $choices = array();
                foreach ($raceOption->getChoices() as $choice){
                    $choices[$choice] = $choice;
                }
                $attr['choices'] = $choices;
            }

            if (($raceOption->getType()==RaceOption::TYPE_RADIO)){
                $attr['expanded'] = true;
                $attr['attr']['class'] = '';
            }

            if (($raceOption->getType()==RaceOption::TYPE_CHECKBOX_READ)){
                $attr['label'] = $raceOption->getTitle().' [[/'.$raceOption->getDocument()->getWebPath().']]';
                $attr['attr']['class'] = 'form-control radio_read';
                $attr['required'] = true;
            }

            if (($raceOption->getType()==RaceOption::TYPE_DOCUMENT))
            {
                $attr['attr']['class'] = '';
                $form->add('document',$raceOption->getTypeClass(),$attr);
            }else{
                $form->add('value', $raceOption->getTypeClass(),$attr);
            }

        });
    }

    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Plopcom\InscriptionsBundle\Entity\AthleteOption'
        ));
    }
}
