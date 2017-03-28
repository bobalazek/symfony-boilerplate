<?php

namespace AppBundle\EventSubscriber;

use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use AppBundle\Service\UserActionsService;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class SecuritySubscriber implements EventSubscriberInterface
{
    protected $tokenStorage;
    protected $authorizationChecker;
    protected $userActionsService;

    public function __construct(
        TokenStorage $tokenStorage,
        AuthorizationChecker $authorizationChecker,
        UserActionsService $userActionsService
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->userActionsService = $userActionsService;
    }

    public function onInteractiveLogin($event)
    {
        $url = $event->getRequest()->getUri();
        if (strpos($url, '/api')) {
            return false;
        }

        $this->userActionsService->add(
            'user.login',
            'User has been logged in!'
        );
    }

    public function onSwitchUser($event)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $targetUser = $event->getTargetUser();

        if ($this->authorizationChecker->isGranted('ROLE_PREVIOUS_ADMIN')) {
            $this->userActionsService->add(
                'user.switch.back',
                'User has switched back to own user (from user with ID "'.$user->getId().'")!',
                [
                    'user_id' => $targetUser->getId(),
                    'from_user_id' => $user->getId(),
                ],
                $targetUser // when we switch back, the target user is actually the admin, that impersonated the user
            );
        } else {
            $this->userActionsService->add(
                'user.switch',
                'User has switched to user with ID "'.$targetUser->getId().'"!',
                [
                    'user_id' => $user->getId(),
                    'to_user_id' => $targetUser->getId(),
                ]
            );
        }
    }

    // TODO: Implement logout event

    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => array('onInteractiveLogin'),
            SecurityEvents::SWITCH_USER => array('onSwitchUser'),
        ];
    }
}
