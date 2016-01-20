<?php

namespace Plopcom\InscriptionsBundle\Controller;

use Plopcom\InscriptionsBundle\Entity\Document;
use Plopcom\InscriptionsBundle\Entity\Address;
use Plopcom\InscriptionsBundle\Entity\Athlete;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Plopcom\InscriptionsBundle\Entity\Inscription;
use Plopcom\InscriptionsBundle\Form\InscriptionType;

/**
 * Inscription controller.
 *
 * @Route("/inscription")
 */
class InscriptionController extends Controller
{
    /**
     * Lists all Inscription entities.
     *
     * @Route("/", name="inscription_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $inscriptions = $em->getRepository('PlopcomInscriptionsBundle:Inscription')->findAll();

        return $this->render('inscription/index.html.twig', array(
            'inscriptions' => $inscriptions,
        ));
    }

    /**
     * Creates a new Inscription entity.
     *
     * @Route("/{slug}/new", name="inscription_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request,$slug)
    {
        $em = $this->getDoctrine()->getManager();
        $race = $em->getRepository('PlopcomInscriptionsBundle:Race')->findOneBySlug($slug);

        if ($race){
            $nb_of_athletes = $race->getNumberOfAthlete();
            if ($nb_of_athletes>0){
                $inscription = new Inscription();

                for($i=0; $i<$nb_of_athletes; $i++){
                    $athlete = new Athlete();
                    $address = new Address();
                    $document = new Document();
                    $athlete->setAddress($address);
//                    $athlete->setDocument($document);
                    $inscription->addAthlete($athlete);
                    $inscription->setRace($race);
                }

                $form = $this->createForm('Plopcom\InscriptionsBundle\Form\InscriptionType', $inscription);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {

                    foreach($inscription->getAthletes() as $athlete){
                        $doc = $athlete->getDocument();
                        if ($doc&&$doc->getFile()){
                            $doc->upload();
                            $athlete->setInscription($inscription);
                        }else{
                            $request->getSession()->getFlashBag()->add('error', 'Le justificatif est obligatoire pour '.$athlete->getFullName());
                            return $this->render('inscription/new.html.twig', array(
                                'inscription' => $inscription,
                                'race' => $race,
                                'form' => $form->createView(),
                            ));
                        }

                    }

                    $inscription->setStatus(Inscription::STATUS_UNCHECKED);
                    $inscription->setPayementStatus(Inscription::PAYEMENT_STATUS_NOT_PAYED);

                    $em->persist($inscription);
                    $em->flush();

                    return $this->redirectToRoute('inscription_show', array('id' => $inscription->getId(),'secret' => $inscription->getSalt()));
                }



                return $this->render('inscription/new.html.twig', array(
                    'inscription' => $inscription,
                    'race' => $race,
                    'form' => $form->createView(),
                ));
            }
        }else{
            $this->get('session')->getFlashBag()->add('error', 'La course identifiée par "'. $slug .'"'." n'a pas été trouvée");
            return $this->redirectToRoute('race_index');
        }
    }

    /**
     * Finds and displays a Inscription entity.
     *
     * @Route("/{id}/{secret}", name="inscription_show")
     * @Method("GET")
     */
    public function showAction(Inscription $inscription,$secret)
    {
        if($secret != $inscription->getSalt()) {
            throw $this->createNotFoundException('Respectez la vie privée des inscrits.');
        }

        $deleteForm = $this->createDeleteForm($inscription);

        return $this->render('inscription/show.html.twig', array(
            'inscription' => $inscription,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Inscription entity.
     *
     * @Route("/{id}/edit/{secret}", name="inscription_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Inscription $inscription,$secret)
    {
        if($secret != $inscription->getSalt()) {
            throw $this->createNotFoundException('Respectez la vie privée des inscrits.');
        }

        $deleteForm = $this->createDeleteForm($inscription);
        $editForm = $this->createForm('Plopcom\InscriptionsBundle\Form\InscriptionType', $inscription);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($inscription);
            $em->flush();

            return $this->redirectToRoute('inscription_edit', array('id' => $inscription->getId(), 'secret' => $inscription->getSalt()));
        }

        return $this->render('inscription/edit.html.twig', array(
            'inscription' => $inscription,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Inscription entity.
     *
     * @Route("/{id}", name="inscription_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, Inscription $inscription)
    {
        $form = $this->createDeleteForm($inscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($inscription);
            $em->flush();
        }

        return $this->redirectToRoute('inscription_index');
    }

    /**
     * Creates a form to delete a Inscription entity.
     *
     * @param Inscription $inscription The Inscription entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Inscription $inscription)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('inscription_delete', array('id' => $inscription->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
