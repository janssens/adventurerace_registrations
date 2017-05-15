<?php
// src/Plopcom/InscriptionsBundle/EventListener/InscriptionOnFlush.php
namespace Plopcom\InscriptionsBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Plopcom\InscriptionsBundle\Entity\Inscription;

class InscriptionOnFlush
{
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if (get_class($entity) == 'Plopcom\InscriptionsBundle\Entity\Inscription') {
                if ($entity->getStatus() == Inscription::STATUS_UNVALID){
                    $changeSet = $uow->getEntityChangeSet($entity);
                    if (isset($changeSet['admin_comment'])){
                        //notify by email
//                        $logger = $this->container->get('logger');
//                        $logger->debug('notify by email inscription #'.$entity->getId());
                        $to = array();
                        foreach($entity->getAthletes() as $athlete){
                            $to[$athlete->getEmail()]=$athlete->getFullName();
                        }
                        $message = \Swift_Message::newInstance()
                            ->setSubject('['.$entity->getRace()->getTitle().'] Notification ')
                            ->setFrom(array('contact@raisaventure.fr' => $entity->getRace()->getTitle()))
                            ->setReplyTo(array($entity->getRace()->getEvent()->getEmail() => $entity->getRace()->getTitle()))
                            ->setTo($to)
                            ->setBcc($entity->getRace()->getEvent()->getEmail())
                            ->setBody(
                                $this->container->get('templating')->render(
                                // app/Resources/views/Emails/notification.html.twig
                                    'Emails/notification.html.twig',
                                    array('inscription' => $entity)
                                ),
                                'text/html'
                            );
                        $this->container->get('mailer')->send($message);
                        $this->container->get('request_stack')->getCurrentRequest()->getSession()->getFlashBag()->add('success', 'Une notification a été envoyée');
                    }
                }
            }
        }

    }
}