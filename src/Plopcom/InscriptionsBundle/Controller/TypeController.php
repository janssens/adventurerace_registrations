<?php

namespace Plopcom\InscriptionsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Plopcom\InscriptionsBundle\Entity\Type;
use Plopcom\InscriptionsBundle\Form\TypeType;
use DateTime;

/**
 * Type controller.
 *
 * @Route("/type")
 */
class TypeController extends Controller
{
    /**
     * Lists all Type entities.
     *
     * @Route("/", name="type_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $types = $em->getRepository('PlopcomInscriptionsBundle:Type')->findAll();

        return $this->render('type/index.html.twig', array(
            'types' => $types,
        ));
    }

    /**
     * Creates a new Type entity.
     *
     * @Route("/new", name="type_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     *
     */
    public function newAction(Request $request)
    {
        $type = new Type();
        $form = $this->createForm('Plopcom\InscriptionsBundle\Form\TypeType', $type);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($type);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Le type de course a été sauvegardé');

            return $this->redirectToRoute('type_show', array('code' => $type->getCode()));
        }

        return $this->render('type/new.html.twig', array(
            'type' => $type,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Type entity.
     *
     * @Route("/{code}", name="type_show")
     * @Method("GET")
     */
    public function showAction(Type $type)
    {
        $deleteForm = $this->createDeleteForm($type);

        $races = $type->getRaces();

        $now = new DateTime();
        $private_races = array();
        $public_races = array();
        $past_public_races = array();
        foreach ($races as $race) {
            if ($race->getPublic()) {
                if ($race->getDate() > $now){
                    $public_races[] = $race;
                }else{
                    $past_public_races[] = $race;
                }
            } else {
                $private_races[] = $race;
            }
        }

        return $this->render('type/show.html.twig', array(
            'type' => $type,
            'private_races' => $private_races,
            'public_races' => $public_races,
            'past_public_races' => $past_public_races,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Type entity.
     *
     * @Route("/{id}/edit", name="type_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     *
     */
    public function editAction(Request $request, Type $type)
    {
        $deleteForm = $this->createDeleteForm($type);
        $editForm = $this->createForm('Plopcom\InscriptionsBundle\Form\TypeType', $type);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($type);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Le type de course a été sauvegardé');

            return $this->redirectToRoute('type_index');
        }

        return $this->render('type/edit.html.twig', array(
            'type' => $type,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Type entity.
     *
     * @Route("/{id}", name="type_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     *
     */
    public function deleteAction(Request $request, Type $type)
    {
        $form = $this->createDeleteForm($type);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($type);
            $em->flush();
        }

        return $this->redirectToRoute('type_index');
    }

    /**
     * Creates a form to delete a Type entity.
     *
     * @param Type $type The Type entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Type $type)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('type_delete', array('id' => $type->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
