<?php

namespace Plopcom\InscriptionsBundle\Controller;

use Plopcom\InscriptionsBundle\Entity\Configuration;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Plopcom\InscriptionsBundle\Entity\Inscription;
use DateTime;
/**
 * Default controller.
 *
 * @Route("/")
 */
class DefaultController extends Controller
{

    /**
     * Lists all Athlete entities.
     *
     * @Route("", name="default_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $events = $em->getRepository('PlopcomInscriptionsBundle:Event')->findAll();

        $events_with_public_races = array();
        $past_events = array();
        foreach ($events as $event) {
            $event_public = false;
            $now = new DateTime();
            $date = $now;
            foreach ($event->getRaces() as $race) {
                if ($race->getPublic()) {
                    $event_public = true;
                    if ($race->getDate() > $date)
                        $date = $race->getDate();
                }
            }
            if ($event_public){
                if ($date > $now)
                    $events_with_public_races[] = $event;
                else
                    $past_events[] = $event;
            }
        }

        $my_events = array();

        $user = $this->getUser();
        if ($user){
            $my_events = $em->getRepository('PlopcomInscriptionsBundle:Event')->findByOwner($user);
        }

        return $this->render('default/index.html.twig', array(
            'conf' => $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('PlopcomInscriptionsBundle:Configuration')
                ->getInstance(),
            'events' => $events,
            'my_events' => $my_events,
            'events_with_public_races' => $events_with_public_races,
            'past_events' => $past_events,
        ));
    }

    /**
     * Edit app.
     *
     * @Route("/admin", name="app_config")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function configAction(Request $request)
    {
        $conf = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('PlopcomInscriptionsBundle:Configuration')
            ->getInstance();

        $editForm = $this->createForm('Plopcom\InscriptionsBundle\Form\ConfigurationType', $conf);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($conf);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'conf saved');

            return $this->redirectToRoute('app_config');
        }

        return $this->render('default/conf.html.twig', array(
            'conf' => $conf,
            'edit_form' => $editForm->createView(),
        ));
    }
}
