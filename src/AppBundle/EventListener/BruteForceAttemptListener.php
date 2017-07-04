<?php

namespace AppBundle\EventListener;

use Anyx\LoginGateBundle\Event\BruteForceAttemptEvent;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class BruteForceAttemptListener
{
    use ContainerAwareTrait;

    public function onBruteForceAttempt(BruteForceAttemptEvent $event)
    {
        // TODO
    }
}
