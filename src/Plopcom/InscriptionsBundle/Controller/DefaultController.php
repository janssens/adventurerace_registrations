<?php

namespace Plopcom\InscriptionsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Plopcom\InscriptionsBundle\Entity\Inscription;
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

        $user = $this->getUser();
        if ($user){
            $my_events = $em->getRepository('PlopcomInscriptionsBundle:Event')->findByOwner($user);
            return $this->render('default/index.html.twig', array(
                'events' => $events,
                'my_events' => $my_events,
            ));
        }

        return $this->render('default/index.html.twig', array(
            'events' => $events,
        ));
    }

}
