<?php

namespace AppBundle\EventSubscriber;

use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\User;
use AppBundle\Service\UserActionsService;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class AuthenticationSubscriber implements EventSubscriberInterface
{
    protected $em;
    protected $userActionsService;

    public function __construct(EntityManager $em, UserActionsService $userActionsService)
    {
        $this->em = $em;
        $this->userActionsService = $userActionsService;
    }

    public function onAuthenticationFailure($event)
    {
        $authenticationTokenUser = $event->getAuthenticationToken()->getUser();

        $user = $this->em
            ->getRepository('AppBundle:User')
            ->findByUsernameOrEmail($authenticationTokenUser);

        $this->userActionsService->add(
            'user.login.fail',
            'User has tried to log in!',
            [
                'username' => $authenticationTokenUser,
            ],
            $user
        );
    }

    public static function getSubscribedEvents()
    {
        return [
            AuthenticationEvents::AUTHENTICATION_FAILURE => array('onAuthenticationFailure'),
        ];
    }
}
