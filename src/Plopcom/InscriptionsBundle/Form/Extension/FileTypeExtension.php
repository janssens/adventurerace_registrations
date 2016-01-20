<?php

namespace Plopcom\InscriptionsBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class FileTypeExtension extends AbstractTypeExtension
{
    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
        return FileType::class;
    }

    /**
     * Add the image_path option
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(array('file_path','file_name'));
    }

    /**
     * Pass the image URL to the view
     *
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (array_key_exists('file_path', $options)) {
            $parentData = $form->getParent()->getData();

            if (null !== $parentData) {
                $accessor = PropertyAccess::createPropertyAccessor();
                $imageUrl = $accessor->getValue($parentData, $options['file_path']);
            } else {
                $imageUrl = null;
            }

            // set an "image_url" variable that will be available when rendering this field
            $view->vars['file_url'] = $imageUrl;
        }
        if (array_key_exists('file_name', $options)) {
            $parentData = $form->getParent()->getData();

            if (null !== $parentData) {
                $accessor = PropertyAccess::createPropertyAccessor();
                $imageName = $accessor->getValue($parentData, $options['file_name']);
            } else {
                $imageName = null;
            }

            // set an "image_url" variable that will be available when rendering this field
            $view->vars['file_name'] = $imageName;
        }
    }
}
