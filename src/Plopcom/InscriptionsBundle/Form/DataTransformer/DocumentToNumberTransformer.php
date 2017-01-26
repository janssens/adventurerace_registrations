<?php
// src/Plopcom/InscriptionsBundle/Form/DataTransformer/DocumentToNumberTransformer.php
namespace Plopcom\InscriptionsBundle\Form\DataTransformer;

use Plopcom\InscriptionsBundle\Entity\Document;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class DocumentToNumberTransformer implements DataTransformerInterface
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Transforms an object (document) to a string (number).
     *
     * @param  Document|null $document
     * @return string
     */
    public function transform($document)
    {
        if (null === $document) {
            return '';
        }

        if (! is_object($document)) {
            return '';
        }

        return $document->getId();
    }

    /**
     * Transforms a string (number) to an object (document).
     *
     * @param  string $documentNumber
     * @return Document|null
     * @throws TransformationFailedException if object (document) is not found.
     */
    public function reverseTransform($documentNumber)
    {
        // no doc number? It's optional, so that's ok
        if (!$documentNumber) {
            return;
        }

        $doc = $this->manager
            ->getRepository('PlopcomInscriptionsBundle:Document')
            // query for the document with this id
            ->find($documentNumber)
        ;

        if (null === $doc) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'A document with number "%s" does not exist!',
                $documentNumber
            ));
        }

        return $doc;
    }
}