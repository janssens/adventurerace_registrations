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
     * IPN from paypal.
     *
     * @Route("/paypalipn", name="paypalipn")
     * @Method("POST")
     *
     */
    public function paypalipnAction(Request $request)
    {
        if ($request->isMethod('POST')) { //only post

            $ipn = new PaypalIPN();
            // Use the sandbox endpoint during testing.
            if ($this->getParameter('plopcominscriptions.paypal.use_sandbox'))
                $ipn->useSandbox();
            $verified = $ipn->verifyIPN();
            if ($verified) {
                /*
                 * Process IPN
                 * A list of variables is available here:
                 * https://developer.paypal.com/webapps/developer/docs/classic/ipn/integration-guide/IPNandPDTVariables/
                 */
                $em = $this->getDoctrine()->getManager();
                $receiver_email = $request->get('receiver_email');
                $race_id = $request->get('item_number');
                if ($race_id){ //race id
                    $race = $em->getRepository('PlopcomInscriptionsBundle:Race')->find($race_id);
                    if ($race){ //race found
                        if ($receiver_email && ($receiver_email == $race->getEvent()->getPaypalAccountEmail())) { // good receiver
                            $invoice_id = $request->get('invoice');
                            if ($invoice_id) { //invoice id
                                $inscription = $em->getRepository('PlopcomInscriptionsBundle:Inscription')->find($invoice_id);
                                if ($inscription) { //inscription found

                                    // check whether the payment_status is Completed
                                    // check that txn_id has not been previously processed
                                    // check that receiver_email is your PayPal email
                                    // check that payment_amount/payment_currency are correct
                                    // process payment and mark item as paid.
                                    // assign posted variables to local variables
                                    //$item_name = $_POST['item_name'];
                                    //$item_number = $_POST['item_number'];
                                    $payment_status = $_POST['payment_status'];
                                    if ($payment_status == 'Completed') { //payement complet

                                        $payment_amount = $_POST['mc_gross'];
                                        if ($payment_amount != $inscription->getTotal()) { //wrong amount
                                            error_log(date('[Y-m-d H:i e] ') . "wrong amount " . PHP_EOL, 3, LOG_FILE);
                                            return $this->redirectToRoute("default_index");
                                        }

                                        if ($inscription->getPayementStatus() != Inscription::PAYEMENT_STATUS_PAYED){
                                            $inscription->setPayementStatus(Inscription::PAYEMENT_STATUS_PAYED);
                                            $em->persist($inscription);
                                            $em->flush();

                                            //email payement ok
                                            $dest = array();
                                            foreach ($inscription->getAthletes() as $athlete) {
                                                $dest[$athlete->getEmail()] = $athlete->getFullName();
                                            }
                                            $message = \Swift_Message::newInstance()
                                                ->setSubject('[' . $race->getTitle() . '] Paiement reçu')
                                                ->setFrom(array($race->getEvent()->getEmail() => $race->getTitle()))
                                                ->setTo($dest)
                                                ->setBcc($race->getEvent()->getEmail())
                                                ->setBody(
                                                    $this->renderView(
                                                    // app/Resources/views/Emails/payement.html.twig
                                                        'Emails/payement.html.twig',
                                                        array('inscription' => $inscription)
                                                    ),
                                                    'text/html'
                                                );
                                            $this->get('mailer')->send($message);
                                        }else{

                                        }

                                    }elseif ($payment_status == 'Refunded'){
                                        $inscription->setPayementStatus(Inscription::PAYEMENT_STATUS_REFUND);
                                        $inscription->setStatus(Inscription::STATUS_DNS);
                                        $em->persist($inscription);
                                        $em->flush();

                                        //email payement ok
                                        $dest = array();
                                        foreach ($inscription->getAthletes() as $athlete) {
                                            $dest[$athlete->getEmail()] = $athlete->getFullName();
                                        }
                                        $message = \Swift_Message::newInstance()
                                            ->setSubject('[' . $race->getTitle() . '] Remboursement')
                                            ->setFrom(array($race->getEvent()->getEmail() => $race->getTitle()))
                                            ->setTo($dest)
                                            ->setBcc($race->getEvent()->getEmail())
                                            ->setBody(
                                                $this->renderView(
                                                // app/Resources/views/Emails/payement.html.twig
                                                    'Emails/refund.html.twig',
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

                                    return $this->redirectToRoute("default_index");
                                }
                            }
                        }
                    }
                }

            }
            // Reply with an empty 200 response to indicate to paypal the IPN was received correctly.
            return new Response('',200);
        }
        return $this->redirectToRoute("default_index");
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
