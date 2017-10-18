<?php

namespace CoreBundle\EventSubscriber;

use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Translation\DataCollectorTranslator;
use Doctrine\ORM\EntityManagerInterface;
use CoreBundle\Entity\User;
use CoreBundle\Manager\UserActionManager;
use CoreBundle\Manager\BruteForceManager;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class AuthenticationSubscriber implements EventSubscriberInterface
{
    protected $em;
    protected $userActionManager;
    protected $requestStack;
    protected $session;
    protected $translator;

    /**
     * @param EntityManager           $em
     * @param UserActionManager       $userActionManager
     * @param BruteForceManager       $bruteForceManager
     * @param DataCollectorTranslator $translator
     */
    public function __construct(
        EntityManagerInterface $em,
        UserActionManager $userActionManager,
        BruteForceManager $bruteForceManager,
        DataCollectorTranslator $translator
    ) {
        $this->em = $em;
        $this->userActionManager = $userActionManager;
        $this->bruteForceManager = $bruteForceManager;
        $this->translator = $translator;
    }

    /**
     * @param AuthenticationFailureEvent $event
     */
    public function onAuthenticationFailure(AuthenticationFailureEvent $event)
    {
        $authenticationTokenUser = $event->getAuthenticationToken()->getUser();

        $user = $this->em
            ->getRepository('CoreBundle:User')
            ->findByUsernameOrEmail($authenticationTokenUser);

        $this->userActionManager->add(
            'user.login.fail',
            $this->translator->trans(
                'login.fail.user_action.text'
            ),
            [
                'username' => $authenticationTokenUser,
            ],
            $user
        );

        $this->bruteForceManager->handleUserBlockedAction(
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
