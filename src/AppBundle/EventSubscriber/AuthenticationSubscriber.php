<?php

namespace AppBundle\EventSubscriber;

use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\User;
use AppBundle\Entity\UserLoginBlock;
use AppBundle\Manager\UserActionManager;
use AppBundle\Manager\BruteForceManager;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class AuthenticationSubscriber implements EventSubscriberInterface
{
    protected $em;
    protected $userActionManager;
    protected $requestStack;
    protected $session;

    public function __construct(
        EntityManager $em,
        UserActionManager $userActionManager,
        BruteForceManager $bruteForceManager
    ) {
        $this->em = $em;
        $this->userActionManager = $userActionManager;
        $this->bruteForceManager = $bruteForceManager;
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

        $this->bruteForceManager->handleUserLoginBlocks(
            $user,
            'login',
            'user.login.fail'
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
