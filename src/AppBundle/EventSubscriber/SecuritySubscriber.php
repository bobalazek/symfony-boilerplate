<?php

namespace AppBundle\EventSubscriber;

use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Translation\DataCollectorTranslator;
use AppBundle\Manager\UserActionManager;
use AppBundle\Manager\TwoFactorAuthenticationManager;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class SecuritySubscriber implements EventSubscriberInterface
{
    protected $tokenStorage;
    protected $authorizationChecker;
    protected $userActionManager;
    protected $twoFactorAuthenticationManager;
    protected $translator;

    /**
     * @param TokenStorage                   $tokenStorage
     * @param AuthorizationChecker           $authorizationChecker
     * @param UserActionManager              $userActionManager
     * @param TwoFactorAuthenticationManager $twoFactorAuthenticationManager
     */
    public function __construct(
        TokenStorage $tokenStorage,
        AuthorizationChecker $authorizationChecker,
        UserActionManager $userActionManager,
        TwoFactorAuthenticationManager $twoFactorAuthenticationManager,
        DataCollectorTranslator $translator
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->userActionManager = $userActionManager;
        $this->twoFactorAuthenticationManager = $twoFactorAuthenticationManager;
        $this->translator = $translator;
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $url = $event->getRequest()->getUri();
        if (strpos($url, '/api')) {
            return false;
        }

        $continuePropagation = $this->twoFactorAuthenticationManager->handle($event);
        if (!$continuePropagation) {
            return false;
        }

        $this->userActionManager->add(
            'user.login',
            'User has been logged in!'
        );
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
