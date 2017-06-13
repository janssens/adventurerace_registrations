<?php

namespace Plopcom\InscriptionsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Plopcom\InscriptionsBundle\Entity\Race;
use Plopcom\InscriptionsBundle\Entity\Inscription;
use Plopcom\InscriptionsBundle\Form\RaceType;
use Plopcom\InscriptionsBundle\Entity\RaceOption;
use Plopcom\InscriptionsBundle\Helper\PaypalIPN;

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

            $rules = $race->getRules();
            if ($rules&&$rules->getFile()){
                $rules->upload();
                $race->setRules($rules);
            }else if(!$rules){
                $race->setRules(null);
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

            $ipn = new PaypalIPN();
            // Use the sandbox endpoint during testing.
            $ipn->useSandbox();
            $verified = $ipn->verifyIPN();
            if ($verified) {
                /*
                 * Process IPN
                 * A list of variables is available here:
                 * https://developer.paypal.com/webapps/developer/docs/classic/ipn/integration-guide/IPNandPDTVariables/
                 */
                $receiver_email = $request->get('receiver_email');
                if ($receiver_email && ($receiver_email == $race->getEvent()->getPaypalAccountEmail())) { // good receiver
                    $invoice_id = $request->get('invoice');
                    if ($invoice_id) { //invoice id
                        $em = $this->getDoctrine()->getManager();
                        $inscription = $em->getRepository('PlopcomInscriptionsBundle:Inscription')->find($invoice_id);
                        if ($inscription) { //inscription found
                            define("DEBUG", 0);
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
                            $paypal_url = $this->getParameter('twig.globals.paypal_url');

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
            // Reply with an empty 200 response to indicate to paypal the IPN was received correctly.
            return new Response('',200);
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
        /*if (!$race->getOpen()) //not open
        {
            if (!$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
                if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                    throw $this->createAccessDeniedException();
                }else if($race->getEvent()->getOwner() != $this->getUser()){
                    throw $this->createAccessDeniedException();
                }
            }
        }*/

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
     * Update inscriptions order.
     *
     * @Route("/{id}/updateinscriptionorder", name="race_update_inscriptions_order")
     * @Method("POST")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function updateInscriptionOrder(Request $request,Race $race)
    {
        $inscriptionsOrder = $request->get('inscriptions');
        $em = $this->getDoctrine()->getManager();
        $inscriptionRepo = $em->getRepository('PlopcomInscriptionsBundle:Inscription');

        foreach ($inscriptionRepo->findById($inscriptionsOrder) as $obj) {
            $obj->setPosition(array_search($obj->getId(), $inscriptionsOrder));
        }

        $em->flush();

        return new Response('<div class="alert alert-success" role="alert">Sauvé</div>', 200);
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
            }else if(!$illu){
                $race->setIllustration(null);
            }

            $rules = $race->getRules();
            if ($rules&&$rules->getFile()){
                $rules->upload();
                $race->setRules($rules);
            }else if(!$rules){
                $race->setRules(null);
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
     * export race starting list as GmCAP format.
     *
     * @Route("/{id}/GmCAPexport", name="race_gmcap_export")
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function exportAsGmCAP(Race $race){
        $return = "Nom\t";
        $return .= "Prénom\t";
        $return .= "Adresse1\t";
        $return .= "Adresse2\t";
        $return .= "Code\t";
        $return .= "Ville\t";
        $return .= "Etat\t";
        $return .= "Pays\t";
        $return .= "Tel\t";
        $return .= "Sexe\t";
        $return .= "Numéro\t";
        $return .= "Licence\t";
        $return .= "Type Licence\t";
        $return .= "Naissance\t";
        $return .= "Catégorie\t";
        $return .= "Nom Catégorie\t";
        $return .= "Abbrev. Catégorie\t";
        $return .= "Nation\t";
        $return .= "Club\t";
        $return .= "Code Club\t";
        $return .= "Competition\t";
        $return .= "Type Compet.\t";
        $return .= "Ville Compet.\t";
        $return .= "Code Ville Compet.\t";
        $return .= "Date Compet.\t";
        $return .= "Course\t";
        $return .= "Distance\t";
        $return .= "Temps\t";
        $return .= "Nb.Secondes\t";
        $return .= "Temps Arrondi\t";
        $return .= "Nb.Secondes Arrondi\t";
        $return .= "Nb.Heures Arrondi\t";
        $return .= "Classement\t";
        $return .= "Classement par Cat.\t";
        $return .= "Organisme\t";
        $return .= "Payé\t";
        $return .= "Invité\t";
        $return .= "Certif Médical\t";
        $return .= "Pris Départ\t";
        $return .= "Abandon\t";
        $return .= "Disqualifié\t";
        $return .= "Qualifié\t";
        $return .= "Envoi Classt\t";
        $return .= "Handicap\t";
        $return .= "ID\t";
        $return .= "Sponsor\t";
        $return .= "Palmares\t";
        $return .= "EMail";
        $return .= "\n";
        foreach ($race->getInscriptions() as $i => $inscription) {
            if ($race->getNumberOfAthlete() > 1){
                foreach ($inscription->getAthletes() as $athlete) {
                    $return .= $athlete->getLastName() . " ";
                }
                $return .= "\t";
                foreach ($inscription->getAthletes() as $athlete) {
                    $return .= $athlete->getFirstName() . " ";
                }
                $return .= "\t";
                $athlete = $inscription->getAthletes()[0];
            }else {
                $athlete = $inscription->getAthletes()[0];
                $return .= $athlete->getLastName() . "\t"; //NOM
                $return .= $athlete->getFirstName() . "\t"; //Prenom
            }
            $return .= $athlete->getAddress()->getLine1()."\t"; //Adresse1
            $return .= $athlete->getAddress()->getLine2()."\t"; //Adresse2
            $return .= $athlete->getAddress()->getZipOrPostcode()."\t"; //Code
            $return .= $athlete->getAddress()->getCity()."\t"; //Ville
            $return .= $athlete->getAddress()->getCountyProvince()."\t"; //Etat
            $return .= $athlete->getAddress()->getCountry()."\t"; //Pays
            $return .= $athlete->getPhone()."\t"; //Tel
            $return .= $inscription->getCategorieLetterEnglish()."\t"; // Sexe
            $return .= ($i+1)."\t"; //TODO: change once order is custom
            $return .= "\t"; //Licence
            $return .= "\t"; //Type Licence
            $return .= $athlete->getDob()->format('Y')."\t"; //Naissance
            $return .= "\t"; //Catégorie
            $return .= "\t"; //Nom Catégorie
            $return .= "\t"; //Abbrev. Catégorie
            $return .= "FRA"."\t"; //Nation
            $return .= $inscription->getTitle()."\t"; //Club
            $return .= "\t"; //Code Club
            $return .= $race->getEvent()->getTitle()."\t"; //Competition
            $return .= $race->getType()->getCode()."\t"; //Type Compet.
            if ($race->getAddress()){
                $return .= $race->getAddress()->getCity()."\t";//Ville Compet.
                $return .= $race->getAddress()->getZipOrPostcode()."\t";//Code Ville Compet.
            }else{
                $return .= "\t";//Ville Compet.
                $return .= "\t";//Code Ville Compet.
            }
            $return .= $race->getDate()->format('d/m/y')."\t";//Date Compet. (jj/mm/yy)
            $return .= $race->getTitle()."\t"; //Course
            $return .= ($race->getDistance()/1000)."\t"; //Distance
            $return .= "00:00:00.00\t"; //Temps
            $return .= "0\t"; //0
            $return .= "00:00:00\t"; //Temps Arrondi\t";
            $return .= "0\t"; //Nb.Secondes Arrondi
            $return .= "0\t"; //Nb.Heures Arrondi
            $return .= "0\t"; //Classement
            $return .= "0\t"; //
            $return .= "\t";//Organisme
            $return .= (($inscription->getPayementStatus() == Inscription::PAYEMENT_STATUS_PAYED) ? 'O' : 'N'). "\t"; //Payé
            $return .= "N\t"; //Invité
            $return .= "N\t";//Certif Médical
            $return .= "O\t";//Pris Départ
            $return .= "N\t";//Abandon
            $return .= "N\t";//Disqualifié
            $return .= "N\t";//Qualifié
            $return .= "N\t";//Envoi Classt
            $return .= "00:00:00\t"; //Handicap
            $return .= "\t";//ID
            $return .= "\t";//Sponsor
            $return .= "\t";//Palmares
            $return .= $athlete->getEmail();//EMail
            $return .= "\n";
        }
        $return = iconv("UTF-8", "windows-1252", $return);

        return new Response($return, 200, array(
            'Content-Encoding: Windows-1252',
            'Content-Type' => 'application/force-download; charset=Windows-1252',
            'Content-Disposition' => 'attachment; filename="'.$race->getSlug().date('dmyhis').'.txt"'
        ));
    }


    /**
     * export race starting list as GmCAP format.
     *
     * @Route("/{id}/CSVexport", name="race_csv_export")
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function exportAsCSV(Race $race){

        $race_option_ids = array();

        $return = "UID\t;\t";
        $return .= "Dossard\t;\t";
        $return .= "Equipe/team\t;\t";
        $return .= "Catégorie\t;\t";
        $return .= "Paiement\t;\t";
        $return .= "Status\t;\t";
        foreach ($race->getOptions() as $option){
            if (!$option->isForAthlete()&&$option->getType()!=RaceOption::TYPE_DOCUMENT){
                $return .= $option->getTitle()."\t;\t";
                $race_option_ids[] = $option->getId();
            }
        }

        for ($i = 0; $i < $race->getNumberOfAthlete(); $i++) {
            $return .= "Nom [".$i."]\t;\t";
            $return .= "Prénom [".$i."]\t;\t";
            $return .= "Adresse [".$i."]\t;\t";
            $return .= "Code Postal [".$i."]\t;\t";
            $return .= "Ville [".$i."]\t;\t";
            $return .= "Pays [".$i."]\t;\t";
            $return .= "Telephone [".$i."]\t;\t";
            $return .= "Année [".$i."]\t;\t";
            $return .= "Email [".$i."]\t;\t";
            foreach ($race->getOptions() as $option){
                if ($option->isForAthlete()&&$option->getType()!=RaceOption::TYPE_DOCUMENT){
                    $return .= $option->getTitle()."[".$i."]\t;\t";
                    $race_option_ids[] = $option->getId();
                }
            }
        }
        $return = rtrim($return,"\t;\t");
        $return .= "\n";

        foreach ($race->getInscriptions() as $i => $inscription) {

            $return .= $inscription->getId()."\t;\t";
            $return .= ($i+1)."\t;\t";//$inscription->getPosition()."\t;\t"; //TODO: change once order is custom
            $return .= $inscription->getTitle()."\t;\t"; //Club
            $return .= $inscription->getCategorieLetterEnglish()."\t;\t"; // Sexe
            $return .= $inscription->getHumanStatus()."\t;\t"; // Paiement
            $return .= $inscription->getHumanPaymentStatus()."\t;\t"; // Status
            foreach ($inscription->getOptions() as $option){
                if (in_array($option->getRaceOption()->getId(),$race_option_ids)) {
                    if ($option->getRaceOption()->getType() != RaceOption::TYPE_DOCUMENT) {
                        $return .= $option->getValue() . "\t;\t";
                    }
                }
            }

            foreach ($inscription->getAthletes() as $athlete) {
                $return .= $athlete->getLastName() . "\t;\t"; //NOM
                $return .= $athlete->getFirstName() . "\t;\t"; //Prenom
                $return .= $athlete->getAddress()->getLine1(); //Adresse1
                $return .= $athlete->getAddress()->getLine2()."\t;\t"; //Adresse2
                $return .= $athlete->getAddress()->getZipOrPostcode()."\t;\t"; //Code
                $return .= $athlete->getAddress()->getCity()."\t;\t"; //Ville
                $return .= $athlete->getAddress()->getCountry()."\t;\t"; //Pays
                $return .= $athlete->getPhone()."\t;\t"; //Tel
                $return .= $athlete->getDob()->format('Y')."\t;\t"; //Naissance
                $return .= $athlete->getEmail()."\t;\t";//EMail
                foreach ($athlete->getOptions() as $option){
                    if (in_array($option->getRaceOption()->getId(),$race_option_ids)) {
                        if ($option->getRaceOption()->getType() != RaceOption::TYPE_DOCUMENT) {
                            $return .= $option->getValue() . "\t;\t";
                        }
                    }
                }
            }
            $return = rtrim($return,"\t;\t");

            $return .= "\n";
        }

        $return = str_replace(',',';',$return);
        $return = str_replace("\t;\t",',',$return);

        return new Response($return, 200, array(
            'Content-Encoding: UTF-8',
            'Content-Type' => 'application/force-download; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$race->getSlug().date('dmyhis').'.csv"'
        ));
    }

    /**
     * export race starting list as GmCAP format.
     *
     * @Route("/{id}/CSVexportPublic", name="race_csv_export_public")
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function exportAsCSVPublic(Race $race){

        $race_option_ids = array();

        $return = "UID\t;\t";
        $return .= "Dossard\t;\t";
        $return .= "Equipe/team\t;\t";
        $return .= "Catégorie\t;\t";

        for ($i = 0; $i < $race->getNumberOfAthlete(); $i++) {
            $return .= "Nom [".$i."]\t;\t";
            $return .= "Prénom [".$i."]\t;\t";
            $return .= "Pays [".$i."]\t;\t";
            $return .= "Année [".$i."]\t;\t";
        }
        $return = rtrim($return,"\t;\t");
        $return .= "\n";

        foreach ($race->getInscriptions() as $i => $inscription) {

            $return .= $inscription->getId()."\t;\t";
            $return .= ($i+1)."\t;\t";//$inscription->getPosition()."\t;\t"; //TODO: change once order is custom
            $return .= $inscription->getTitle()."\t;\t"; //Club
            $return .= $inscription->getCategorieLetterEnglish()."\t;\t"; // Sexe

            foreach ($inscription->getAthletes() as $athlete) {
                $return .= $athlete->getLastName() . "\t;\t"; //NOM
                $return .= $athlete->getFirstName() . "\t;\t"; //Prenom
                $return .= $athlete->getAddress()->getCountry()."\t;\t"; //Pays
                $return .= $athlete->getDob()->format('Y')."\t;\t"; //Naissance
            }
            $return = rtrim($return,"\t;\t");
            $return .= "\n";
        }

        $return = str_replace(',',';',$return);
        $return = str_replace("\t;\t",',',$return);

        return new Response($return, 200, array(
            'Content-Encoding: UTF-8',
            'Content-Type' => 'application/force-download; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$race->getSlug()."-public-".date('dmyhis').'.csv"'
        ));
    }

    /**
     * export race starting list as GmCAP format.
     *
     * @Route("/{id}/mailsexport", name="race_mails_export")
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function exportEmails(Race $race){
        $return = array();
        foreach ($race->getInscriptions() as $i => $inscription) {
            foreach ($inscription->getAthletes() as $athlete) {
                    $return[] = trim($athlete->getEmail());
            }
        }

        $return = iconv("UTF-8", "windows-1252", implode(',',array_unique($return)));

        return new Response($return, 200, array(
            'Content-Encoding: Windows-1252',
            'Content-Type' => 'application/force-download; charset=Windows-1252',
            'Content-Disposition' => 'attachment; filename="'.$race->getSlug().date('dmyhis').'_emails.txt"'
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
