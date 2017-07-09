<?php

namespace AppBundle\EventSubscriber;

use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\User;
use AppBundle\Manager\UserActionManager;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class AuthenticationSubscriber implements EventSubscriberInterface
{
    protected $em;
    protected $userActionManager;

    public function __construct(
        EntityManager $em,
        UserActionManager $userActionManager
    ) {
        $this->em = $em;
        $this->userActionManager = $userActionManager;
    }

    /**
     * @param AuthenticationFailureEvent $event
     */
    public function onAuthenticationFailure(AuthenticationFailureEvent $event)
    {
        $authenticationTokenUser = $event->getAuthenticationToken()->getUser();

        $user = $this->em
            ->getRepository('AppBundle:User')
            ->findByUsernameOrEmail($authenticationTokenUser);

        $this->userActionManager->add(
            'user.login.fail',
            'User has tried to log in!',
            [
                'username' => $authenticationTokenUser,
            ],
            $user
        );
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            AuthenticationEvents::AUTHENTICATION_FAILURE => ['onAuthenticationFailure'],
        ];
    }
}
