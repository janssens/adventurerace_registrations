<?php
// src/Plopcom/InscriptionsBundle/Entity/User.php

namespace Plopcom\InscriptionsBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="Event", mappedBy="owner", cascade={"persist", "remove", "merge"})
     * 
     */
    protected $events;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    /**
     * Add event
     *
     * @param \Plopcom\InscriptionsBundle\Entity\Event $event
     *
     * @return User
     */
    public function addEvent(\Plopcom\InscriptionsBundle\Entity\Event $event)
    {
        $this->events[] = $event;

        return $this;
    }

    /**
     * Remove event
     *
     * @param \Plopcom\InscriptionsBundle\Entity\Event $event
     */
    public function removeEvent(\Plopcom\InscriptionsBundle\Entity\Event $event)
    {
        $this->events->removeElement($event);
    }

    /**
     * Get events
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEvents()
    {
        return $this->events;
    }
}
