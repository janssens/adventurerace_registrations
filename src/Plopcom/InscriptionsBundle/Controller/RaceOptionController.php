<?php

namespace Plopcom\InscriptionsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Plopcom\InscriptionsBundle\Entity\RaceOption;
use Plopcom\InscriptionsBundle\Form\RaceOptionType;

/**
 * RaceOption controller.
 *
 * @Route("/raceoption")
 */
class RaceOptionController extends Controller
{
    /**
     * Lists all RaceOption entities.
     *
     * @Route("/", name="raceoption_index")
     * @Method("GET")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $raceOptions = $em->getRepository('PlopcomInscriptionsBundle:RaceOption')->findAll();

        return $this->render('raceoption/index.html.twig', array(
            'raceOptions' => $raceOptions,
        ));
    }

    /**
     * Creates a new RaceOption entity.
     *
     * @Route("/new", name="raceoption_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $raceOption = new RaceOption();
        $raceOption->setChoices(array());
        $form = $this->createForm('Plopcom\InscriptionsBundle\Form\RaceOptionType', $raceOption);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($raceOption);
            $em->flush();

            return $this->redirectToRoute('raceoption_show', array('id' => $raceOption->getId()));
        }

        return $this->render('raceoption/new.html.twig', array(
            'raceOption' => $raceOption,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a RaceOption entity.
     *
     * @Route("/{id}", name="raceoption_show")
     * @Method("GET")
     */
    public function showAction(RaceOption $raceOption)
    {
        $deleteForm = $this->createDeleteForm($raceOption);

        return $this->render('raceoption/show.html.twig', array(
            'raceOption' => $raceOption,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing RaceOption entity.
     *
     * @Route("/{id}/edit", name="raceoption_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function editAction(Request $request, RaceOption $raceOption)
    {
        $deleteForm = $this->createDeleteForm($raceOption);
        $editForm = $this->createForm('Plopcom\InscriptionsBundle\Form\RaceOptionType', $raceOption);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($raceOption);
            $em->flush();

            return $this->redirectToRoute('raceoption_show', array('id' => $raceOption->getId()));
        }

        return $this->render('raceoption/edit.html.twig', array(
            'raceOption' => $raceOption,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a RaceOption entity.
     *
     * @Route("/{id}", name="raceoption_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function deleteAction(Request $request, RaceOption $raceOption)
    {
        $form = $this->createDeleteForm($raceOption);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($raceOption);
            $em->flush();
        }

        return $this->redirectToRoute('raceoption_index');
    }

    /**
     * Creates a form to delete a RaceOption entity.
     *
     * @param RaceOption $raceOption The RaceOption entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(RaceOption $raceOption)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('raceoption_delete', array('id' => $raceOption->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
