<?php

namespace Plopcom\InscriptionsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Plopcom\InscriptionsBundle\Entity\Race;
use Plopcom\InscriptionsBundle\Entity\Inscription;
use Plopcom\InscriptionsBundle\Form\RaceType;

/**
 * Race controller.
 *
 * @Route("/race")
 */
class RaceController extends Controller
{
    /**
     * Lists all Race entities.
     *
     * @Route("/", name="race_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $races = $em->getRepository('PlopcomInscriptionsBundle:Race')->findAll();

        return $this->render('race/index.html.twig', array(
            'races' => $races,
        ));
    }

    /**
     * Creates a new Race entity.
     *
     * @Route("/new/{event_id}", name="race_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request,$event_id)
    {
        $race = new Race();
        $form = $this->createForm('Plopcom\InscriptionsBundle\Form\RaceType', $race);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $illu = $race->getIllustration();
            if ($illu&&$illu->getFile()){
                $illu->upload();
                $race->setIllustration($illu);
            }else{
                $race->setIllustration(null);
            }

            $event = $em->getRepository('PlopcomInscriptionsBundle:Event')->find($event_id);
            if ($event){
                $race->setEvent($event);
                $em->persist($race);
                $em->flush();

                return $this->redirectToRoute('race_show', array('slug' => $race->getSlug()));
            }else{
                $this->get('session')->getFlashBag()->add('error', "aucun événement d'id #". $event_id .' trouvé');
                return $this->redirectToRoute('race_index');
            }

        }

        return $this->render('race/new.html.twig', array(
            'race' => $race,
            'form' => $form->createView(),
        ));
    }

    /**
     * IPN from paypal.
     *
     * @Route("/{slug}/paypalipn", name="race_paypalipn")
     * @Method("POST")
     *
     */
    public function paypalipnAction(Race $race,Request $request)
    {
        if ($request->isMethod('POST')) { //only post
            $receiver_email = $request->get('receiver_email');
            if ($receiver_email && ($receiver_email == $race->getEvent()->getPaypalAccountEmail())) { // good receiver
                $invoice_id = $request->get('invoice');
                if ($invoice_id) { //invoice id
                    $em = $this->getDoctrine()->getManager();
                    $inscription = $em->getRepository('PlopcomInscriptionsBundle:Inscription')->find($invoice_id);
                    if ($inscription) { //inscription found
                        $payment_status = $request->get('payment_status');
                        if ($payment_status == 'Completed') { //payement complet
                            $inscription->setPayementStatus(Inscription::PAYEMENT_STATUS_PAYED);
                            $em->persist($inscription);
                            $em->flush();

                            //email payement ok
                            $dest = array();
                            foreach($inscription->getAthletes() as $athlete){
                                $dest[$athlete->getEmail()]=$athlete->getFullName();
                            }
                            $message = \Swift_Message::newInstance()
                                ->setSubject('['.$race->getTitle().'] Paiement reçu')
                                ->setFrom(array($race->getEvent()->getEmail() => $race->getTitle()))
                                ->setTo($dest)
                                ->setCc("janssensgaetan@gmail.com")
                                ->setBody(
                                    $this->renderView(
                                    // app/Resources/views/Emails/registration.html.twig
                                        'Emails/payement.html.twig',
                                        array('inscription' => $inscription)
                                    ),
                                    'text/html'
                                );
                            $this->get('mailer')->send($message);

                        } else {
                            $inscription->setPayementStatus(Inscription::PAYEMENT_STATUS_FAILED);
                            $inscription->setAdminComment($payment_status);
                            $em->persist($inscription);
                            $em->flush();
                        }
                        return $this->redirectToRoute("race_show",array('slug' => $race->getSlug()));
                    }
                }
            }
        }
        return $this->redirectToRoute("default_index");
    }

    /**
     * Finds and displays a Race entity.
     *
     * @Route("/{slug}", name="race_show")
     * @Method("GET")
     */
    public function showAction(Request $request,Race $race)
    {
        $deleteForm = $this->createDeleteForm($race);

        if ($request->get("msg")){
            switch($request->get("msg")){
                case 'full':
                    $this->get('session')->getFlashBag()->add('warning', 'Désolé! La course est complète');
                    break;
                case 'cancel':
                    $this->get('session')->getFlashBag()->add('error', 'Vous avez annulé le payment, essayez à nouveau');
                    break;
                case 'succes':
                case 'success':
                case 'yes':
                    $this->get('session')->getFlashBag()->add('success', 'Votre payement a été enregistré, merci.');
                    break;
            }
        }

        return $this->render('race/show.html.twig', array(
            'race' => $race,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Race entity.
     *
     * @Route("/{id}/edit", name="race_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, Race $race)
    {
        $deleteForm = $this->createDeleteForm($race);
        $editForm = $this->createForm('Plopcom\InscriptionsBundle\Form\RaceType', $race);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $illu = $race->getIllustration();
            if ($illu&&$illu->getFile()){
                $illu->upload();
                $race->setIllustration($illu);
            }else{
                $race->setIllustration(null);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($race);
            $em->flush();

            return $this->redirectToRoute('race_edit', array('id' => $race->getId()));
        }

        return $this->render('race/edit.html.twig', array(
            'race' => $race,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Race entity.
     *
     * @Route("/{id}", name="race_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, Race $race)
    {
        $form = $this->createDeleteForm($race);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($race);
            $em->flush();
        }

        return $this->redirectToRoute('race_index');
    }

    /**
     * Creates a form to delete a Race entity.
     *
     * @param Race $race The Race entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Race $race)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('race_delete', array('id' => $race->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
