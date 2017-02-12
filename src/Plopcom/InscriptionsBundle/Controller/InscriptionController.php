<?php

namespace Plopcom\InscriptionsBundle\Controller;

use Plopcom\InscriptionsBundle\Entity\AthleteOptionDocument;
use Plopcom\InscriptionsBundle\Entity\AthleteOptionString;
use Plopcom\InscriptionsBundle\Entity\Document;
use Plopcom\InscriptionsBundle\Entity\Address;
use Plopcom\InscriptionsBundle\Entity\Athlete;
use Plopcom\InscriptionsBundle\Entity\InscriptionOptionDocument;
use Plopcom\InscriptionsBundle\Entity\InscriptionOptionString;
use Plopcom\InscriptionsBundle\Entity\RaceOption;
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

            if (!$race->getOpen()){
                if($race->getEvent()->getOwner() != $this->getUser() && !$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN') )
                    throw $this->createNotFoundException('Inscriptions fermées');
            }
            if (count($race->getInscriptions())>=$race->getMaxAttendee()){
                $race->setOpen(false);
                $em->flush();
                $this->get('session')->getFlashBag()->add('error', 'La course est complète');
                return $this->redirectToRoute('race_show',array('slug'=>$race->getSlug()));
            }

            $nb_of_athletes = $race->getNumberOfAthlete();
            if ($nb_of_athletes>0){
                $inscription = new Inscription();

                for($i=0; $i<$nb_of_athletes; $i++){
                    $athlete = new Athlete();
                    $address = new Address();
                    $athlete->setDob(new \DateTime("20 years ago"));
                    $address->setCountry('FR');
                    $athlete->setAddress($address);
                    $athlete->setInscription($inscription);
                    $inscription->addAthlete($athlete);
                    $inscription->setRace($race);
                }
                
                foreach ($race->getOptions() as $option){
                    if ($option->isForAthlete()){
                        foreach ($inscription->getAthletes() as $athlete)
                        {
                            if ($option->getType() == RaceOption::TYPE_DOCUMENT)
                                $athlete_option = new AthleteOptionDocument();
                            else
                                $athlete_option = new AthleteOptionString();
                            $athlete_option->setRaceOption($option);
                            $athlete_option->setAthlete($athlete);
                            $athlete->addOption($athlete_option);
                        }
                    }else{
                        if ($option->getType() == RaceOption::TYPE_DOCUMENT)
                            $inscription_option = new InscriptionOptionDocument();
                        else
                            $inscription_option = new InscriptionOptionString();
                        $inscription_option->setRaceOption($option);
                        $inscription_option->setInscription($inscription);
                        $inscription->addOption($inscription_option);
                    }
                }

                $form = $this->createForm('Plopcom\InscriptionsBundle\Form\InscriptionType', $inscription);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    
                    foreach($inscription->getAthletes() as $athlete){
                        $doc = $athlete->getDocument();
                        if ($doc&&$doc->getFile()){
                            $doc->upload();
                            $athlete->setInscription($inscription);
                        }else if($race->getDocumentRequired()) {
                            $request->getSession()->getFlashBag()->add('error', 'Le justificatif est obligatoire pour '.$athlete->getFullName());
                            return $this->render('inscription/new.html.twig', array(
                                'inscription' => $inscription,
                                'race' => $race,
                                'form' => $form->createView(),
                            ));
                        }
                        foreach ($athlete->getOptions() as $option_athlete){
                            if ($option_athlete instanceof AthleteOptionDocument){
                                $doc = $option_athlete->getDocument();
                                if ($doc&&$doc->getFile()){
                                    $doc->upload();
                                }
                            }
                        }
                    }

                    if ($inscription->getOptions() != null){
                        foreach ($inscription->getOptions() as $option_inscription){
                            if ($option_inscription instanceof InscriptionOptionDocument){
                                $doc = $option_inscription->getDocument();
                                if ($doc&&$doc->getFile()){
                                    $doc->upload();
                                }
                            }
                        }
                    }

                    $inscription->setStatus(Inscription::STATUS_UNCHECKED);
                    $inscription->setPayementStatus(Inscription::PAYEMENT_STATUS_NOT_PAYED);

                    //last inscription?
                    if (count($race->getInscriptions())+1==$race->getMaxAttendee()){
                        $race->setOpen(false);
                    }

                    $em->persist($inscription);
                    $em->flush();

                    //first email
                    $dest = array();
                    foreach($inscription->getAthletes() as $athlete){
                        $dest[$athlete->getEmail()]=$athlete->getFullName();
                    }
                    $message = \Swift_Message::newInstance()
                        ->setSubject('['.$race->getTitle().'] Bienvenue ')
                        ->setFrom(array($race->getEvent()->getEmail() => $race->getTitle()))
                        ->setTo($dest)
                        ->setBcc($race->getEvent()->getEmail())
                        ->setBody(
                            $this->renderView(
                            // app/Resources/views/Emails/registration.html.twig
                                'Emails/registration.html.twig',
                                array('inscription' => $inscription)
                            ),
                            'text/html'
                        );
                    $this->get('mailer')->send($message);

                    return $this->redirectToRoute('inscription_show', array('id' => $inscription->getId(),'secret' => $inscription->getSalt()));
                }elseif (!$form->isValid()){
                    foreach ($form->getErrors() as $id => $error){
                        $request->getSession()->getFlashBag()->add('error', $error->getMessage());
                    }
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
     * Notify athletes.
     *
     * @Route("/notify/{id}/{secret}", name="inscription_notify")
     * @Method("GET")
     */
    public function notifyAction(Request $request,Inscription $inscription,$secret)
    {
        if($secret != $inscription->getSalt()) {
            throw $this->createNotFoundException('Respectez la vie privée des inscrits.');
        }
        if($inscription->getRace()->getEvent()->getOwner() != $this->getUser() && !$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN') )
            throw $this->createAccessDeniedException();

        //notify by email
        $to = array();
        foreach($inscription->getAthletes() as $athlete){
            $to[$athlete->getEmail()]=$athlete->getFullName();
        }
        $message = \Swift_Message::newInstance()
            ->setSubject('['.$inscription->getRace()->getTitle().'] Notification ')
            ->setFrom(array($inscription->getRace()->getEvent()->getEmail() => $inscription->getRace()->getTitle()))
            ->setTo($to)
            ->setBcc($inscription->getRace()->getEvent()->getEmail())
            ->setBody(
                $this->renderView(
                // app/Resources/views/Emails/notification.html.twig
                    'Emails/notification.html.twig',
                    array('inscription' => $inscription)
                ),
                'text/html'
            );
        $this->get('mailer')->send($message);

        $request->getSession()->getFlashBag()->add('success', 'email envoyé');

        return $this->redirectToRoute('inscription_show',array('id'=>$inscription->getId(),'secret'=>$secret));
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

            foreach($inscription->getAthletes() as $athlete){
                $doc = $athlete->getDocument();
                if ($doc&&$doc->getFile()){
                    $doc->upload();
                }else if(!$doc &&  $inscription->getRace()->getDocumentRequired()){
                    $request->getSession()->getFlashBag()->add('error', 'Le justificatif est obligatoire pour '.$athlete->getFullName());
                    return $this->render('inscription/edit.html.twig', array(
                        'inscription' => $inscription,
                        'edit_form' => $editForm->createView(),
                        'race' => $inscription->getRace(),
                        'delete_form' => $deleteForm->createView(),
                    ));
                }
                foreach ($athlete->getOptions() as $option){
                    if ($option instanceof AthleteOptionDocument){
                        $doc = $option->getDocument();
                        if ($doc&&$doc->getFile()){
                            $doc->upload();
                        }
                    }
                }
            }

            foreach ($inscription->getOptions() as $option_inscription){
                if ($option_inscription instanceof InscriptionOptionDocument){
                    $doc = $option_inscription->getDocument();
                    if ($doc&&$doc->getFile()){
                        $doc->upload();
                    }
                }
            }


            if($inscription->getRace()->getEvent()->getOwner() != $this->getUser() && !$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN') )
                $inscription->setStatus(Inscription::STATUS_UNCHECKED);

            $em = $this->getDoctrine()->getManager();
            $em->persist($inscription);
            $em->flush();

            return $this->redirectToRoute('inscription_show', array('id' => $inscription->getId(), 'secret' => $inscription->getSalt()));
        }

        return $this->render('inscription/edit.html.twig', array(
            'inscription' => $inscription,
            'edit_form' => $editForm->createView(),
            'race' => $inscription->getRace(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    
    /**
     * Notify case unpaid
     *
     * @Route("/notifyUnpaid/{id}/{secret}", name="inscription_notify_unpaid")
     * @Method("GET")
     */
    public function notifyUnpaidAction(Request $request,Inscription $inscription,$secret)
    {
        if($secret != $inscription->getSalt()) {
            throw $this->createNotFoundException('Respectez la vie privée des inscrits.');
        }
        if($inscription->getRace()->getEvent()->getOwner() != $this->getUser() && !$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN') )
            throw $this->createAccessDeniedException();

        //notify by email
        $to = array();
        foreach($inscription->getAthletes() as $athlete){
            $to[$athlete->getEmail()]=$athlete->getFullName();
        }
        $message = \Swift_Message::newInstance()
            ->setSubject('['.$inscription->getRace()->getTitle().'] Paiement en attente ')
            ->setFrom(array($inscription->getRace()->getEvent()->getEmail() => $inscription->getRace()->getTitle()))
            ->setTo($to)
            ->setBcc($inscription->getRace()->getEvent()->getEmail())
            ->setBody(
                $this->renderView(
                // app/Resources/views/Emails/notification.html.twig
                    'Emails/notifyunpaid.html.twig',
                    array('inscription' => $inscription)
                ),
                'text/html'
            );
        $this->get('mailer')->send($message);

        $request->getSession()->getFlashBag()->add('success', 'email envoyé');

        return $this->redirectToRoute('race_show',array('slug'=>$inscription->getRace()->getSlug()));
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
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            if ($inscription->getRace()->getEvent()->getOwner() != $this->getUser()) {
                throw $this->createAccessDeniedException();
            }
        }

        $form = $this->createDeleteForm($inscription);
        $form->handleRequest($request);

        $race = $inscription->getRace();

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($inscription);
            $em->flush();
        }

        return $this->redirectToRoute('race_show',array('slug'=>$race->getSlug()));
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
