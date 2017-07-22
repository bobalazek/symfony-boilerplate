<?php

namespace TfaBundle\EventSubscriber;

use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TfaBundle\Manager\TwoFactorAuthenticationManager;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class SecuritySubscriber implements EventSubscriberInterface
{
    protected $twoFactorAuthenticationManager;

    /**
     * @param TwoFactorAuthenticationManager $twoFactorAuthenticationManager
     */
    public function __construct(
        TwoFactorAuthenticationManager $twoFactorAuthenticationManager
    ) {
        $this->twoFactorAuthenticationManager = $twoFactorAuthenticationManager;
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $continuePropagation = $this->twoFactorAuthenticationManager->handle($event);
        if (!$continuePropagation) {
            return $event->stopPropagation();
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => [
                ['onInteractiveLogin', 32],
            ],
        ];
    }
}
