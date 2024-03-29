<?php

namespace CoreBundle\EventSubscriber;

use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use CoreBundle\Manager\UserActionManager;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class SecuritySubscriber implements EventSubscriberInterface
{
    protected $tokenStorage;
    protected $authorizationChecker;
    protected $userActionManager;
    protected $translator;

    /**
     * @param TokenStorageInterface         $tokenStorage
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param UserActionManager             $userActionManager
     * @param TranslatorInterface           $translator
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        UserActionManager $userActionManager,
        TranslatorInterface $translator
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->userActionManager = $userActionManager;
        $this->translator = $translator;
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $url = $event->getRequest()->getUri();
        /*
         * In the api are that would be triggered each time the user would
         *   do a request, so we only log the login action, if the user
         *   has logged in via the normal form.
         */
        if (false === strpos($url, '/api')) {
            $this->userActionManager->add(
                'user.login',
                $this->translator->trans(
                    'login.user_action.text'
                )
            );
        }
    }

    /**
     * @param SwitchUserEvent $event
     */
    public function onSwitchUser(SwitchUserEvent $event)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $targetUser = $event->getTargetUser();

        if ($this->authorizationChecker->isGranted('ROLE_PREVIOUS_ADMIN')) {
            $this->userActionManager->add(
                'user.switch.back',
                /* @Meaning("Available arguments: %user_id%") */
                $this->translator->trans(
                    'admin.users.switch_user.back.user_action.text',
                    [
                        '%user_id%' => $user->getId(),
                    ]
                ),
                [
                    'user_id' => $targetUser->getId(),
                    'from_user_id' => $user->getId(),
                ],
                $targetUser // when we switch back, the target user is actually the admin, that impersonated the user
            );
        } else {
            $this->userActionManager->add(
                'user.switch',
                /* @Meaning("Available arguments: %target_user_id%") */
                $this->translator->trans(
                    'admin.users.switch_user.into.user_action.text',
                    [
                        '%target_user_id%' => $targetUser->getId(),
                    ]
                ),
                [
                    'user_id' => $user->getId(),
                    'to_user_id' => $targetUser->getId(),
                ]
            );
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => ['onInteractiveLogin'],
            SecurityEvents::SWITCH_USER => ['onSwitchUser'],
        ];
    }
}
