<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var Session
     */
    private $session;

    public function __construct(EntityManagerInterface $em, Session $session)
    {
        $this->em = $em;
        $this->session = $session;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        /** @var User $user */
        // Get the User entity.
        $user = $event->getAuthenticationToken()->getUser();

        // Update your field here.
        $user->setLastLogin(new \DateTime());

        // Persist the data to database.
        $this->em->persist($user);
        $this->em->flush();

        // welcome flash message to user
        $this->session->getFlashBag()->add('success','Hello '.$user->getUsername());
    }
}