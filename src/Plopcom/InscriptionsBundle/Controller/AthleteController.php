<?php

namespace Plopcom\InscriptionsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Plopcom\InscriptionsBundle\Entity\Athlete;
use Plopcom\InscriptionsBundle\Form\AthleteType;

/**
 * Athlete controller.
 *
 * @Route("/athlete")
 */
class AthleteController extends Controller
{
    /**
     * Lists all Athlete entities.
     *
     * @Route("/", name="athlete_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $athletes = $em->getRepository('PlopcomInscriptionsBundle:Athlete')->findAll();

        return $this->render('athlete/index.html.twig', array(
            'athletes' => $athletes,
        ));
    }

    /**
     * Creates a new Athlete entity.
     *
     * @Route("/new", name="athlete_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $athlete = new Athlete();
        $form = $this->createForm('Plopcom\InscriptionsBundle\Form\AthleteType', $athlete);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($athlete);
            $em->flush();

            return $this->redirectToRoute('athlete_show', array('id' => $athlete->getId()));
        }

        return $this->render('athlete/new.html.twig', array(
            'athlete' => $athlete,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Athlete entity.
     *
     * @Route("/{id}", name="athlete_show")
     * @Method("GET")
     */
    public function showAction(Athlete $athlete)
    {
        $deleteForm = $this->createDeleteForm($athlete);

        return $this->render('athlete/show.html.twig', array(
            'athlete' => $athlete,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Athlete entity.
     *
     * @Route("/{id}/edit", name="athlete_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Athlete $athlete)
    {
        $deleteForm = $this->createDeleteForm($athlete);
        $editForm = $this->createForm('Plopcom\InscriptionsBundle\Form\AthleteType', $athlete);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($athlete);
            $em->flush();

            return $this->redirectToRoute('athlete_edit', array('id' => $athlete->getId()));
        }

        return $this->render('athlete/edit.html.twig', array(
            'athlete' => $athlete,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Athlete entity.
     *
     * @Route("/{id}", name="athlete_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Athlete $athlete)
    {
        $form = $this->createDeleteForm($athlete);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($athlete);
            $em->flush();
        }

        return $this->redirectToRoute('athlete_index');
    }

    /**
     * Creates a form to delete a Athlete entity.
     *
     * @param Athlete $athlete The Athlete entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Athlete $athlete)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('athlete_delete', array('id' => $athlete->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
