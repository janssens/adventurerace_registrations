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

            $message = \Swift_Message::newInstance()
                ->setSubject('['.$race->getTitle().'] IPN')
                ->setFrom(array($race->getEvent()->getEmail() => $race->getTitle()))
                ->setTo('janssensgaetan@gmail.com')
                ->setBody(print_r($_POST,true));
            $this->get('mailer')->send($message);

            $receiver_email = $request->get('receiver_email');
            if ($receiver_email && ($receiver_email == $race->getEvent()->getPaypalAccountEmail())) { // good receiver
                $invoice_id = $request->get('invoice');
                if ($invoice_id) { //invoice id
                    $em = $this->getDoctrine()->getManager();
                    $inscription = $em->getRepository('PlopcomInscriptionsBundle:Inscription')->find($invoice_id);
                    if ($inscription) { //inscription found
                        define("DEBUG", 1);
                        // Set to 0 once you're ready to go live
                        define("LOG_FILE", "/var/log/ipn.log");

                        $myPost = $_POST;
                        // read the post from PayPal system and add 'cmd'
                        $req = 'cmd=_notify-validate';
                        foreach ($myPost as $key => $value) {
                            $value = urlencode($value);
                            $req .= "&$key=$value";
                        }
                        // Post IPN data back to PayPal to validate the IPN data is genuine
                        // Without this step anyone can fake IPN data
                        $paypal_url = RACE::PAYPAL_URL;

                        $ch = curl_init($paypal_url);
                        if ($ch == FALSE) {
                            return FALSE;
                        }
                        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
                        if(DEBUG == true) {
                            curl_setopt($ch, CURLOPT_HEADER, 1);
                            curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
                        }
                        // CONFIG: Optional proxy configuration
                        //curl_setopt($ch, CURLOPT_PROXY, $proxy);
                        //curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
                        // Set TCP timeout to 30 seconds
                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
                        // CONFIG: Please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set the directory path
                        // of the certificate as shown below. Ensure the file is readable by the webserver.
                        // This is mandatory for some environments.
                        //$cert = __DIR__ . "./cacert.pem";
                        //curl_setopt($ch, CURLOPT_CAINFO, $cert);
                        $res = curl_exec($ch);
                        if (curl_errno($ch) != 0) // cURL error
                        {
                            if(DEBUG == true) {
                                error_log(date('[Y-m-d H:i e] '). "Can't connect to PayPal to validate IPN message: " . curl_error($ch) . PHP_EOL, 3, LOG_FILE);
                            }
                            curl_close($ch);
                            exit;
                        } else {
                            // Log the entire HTTP response if debug is switched on.
                            if(DEBUG == true) {
                                error_log(date('[Y-m-d H:i e] '). "HTTP request of validation request:". curl_getinfo($ch, CURLINFO_HEADER_OUT) ." for IPN payload: $req" . PHP_EOL, 3, LOG_FILE);
                                error_log(date('[Y-m-d H:i e] '). "HTTP response of validation request: $res" . PHP_EOL, 3, LOG_FILE);
                            }
                            curl_close($ch);
                        }
                        // Inspect IPN validation result and act accordingly
                        // Split response headers and payload, a better way for strcmp
                        $tokens = explode("\r\n\r\n", trim($res));
                        $res = trim(end($tokens));
                        if (strcmp ($res, "VERIFIED") == 0) {
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
                                if ($payment_amount != $inscription->getRace()->getEntryFees()){ //wrong amount
                                    error_log(date('[Y-m-d H:i e] '). "wrong amount ". PHP_EOL, 3, LOG_FILE);
                                    return $this->redirectToRoute("default_index");
                                }

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
                                    ->setBcc('janssensgaetan@gmail.com')
                                    ->setBody(
                                        $this->renderView(
                                        // app/Resources/views/Emails/payement.html.twig
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


                            //$payment_currency = $_POST['mc_currency'];
                            //$txn_id = $_POST['txn_id'];
                            //$payer_email = $_POST['payer_email'];

                            if(DEBUG == true) {
                                error_log(date('[Y-m-d H:i e] '). "Verified IPN: $req ". PHP_EOL, 3, LOG_FILE);
                            }
                        } else if (strcmp ($res, "INVALID") == 0) {
                            // log for manual investigation
                            // Add business logic here which deals with invalid IPN messages
                            $inscription->setPayementStatus(Inscription::PAYEMENT_STATUS_FAILED);
                            $inscription->setAdminComment($res);
                            $em->persist($inscription);
                            $em->flush();

                            if(DEBUG == true) {
                                error_log(date('[Y-m-d H:i e] '). "Invalid IPN: $req" . PHP_EOL, 3, LOG_FILE);
                            }
                        }
                        return $this->redirectToRoute("default_index");
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
        if (!$race->getOpen()) //not open
        {
            if (!$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
                if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                    throw $this->createAccessDeniedException();
                }else if($race->getEvent()->getOwner() != $this->getUser()){
                    throw $this->createAccessDeniedException();
                }
            }
        }

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
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            if ($race->getEvent()->getOwner() != $this->getUser()) {
                throw $this->createAccessDeniedException();
            }
        }

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

            return $this->redirectToRoute('race_show', array('slug' => $race->getSlug()));
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

        $event = $race->getEvent();

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($race);
            $em->flush();
        }

        return $this->redirectToRoute('event_show',array('slug' => $event->getSlug()));
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
