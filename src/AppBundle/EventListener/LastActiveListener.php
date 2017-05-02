<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use AppBundle\Entity\User;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class LastActiveListener
{
    use ContainerAwareTrait;

    public function onKernelController(FilterControllerEvent $event)
    {
        $tokenStorage = $this->container->get('security.token_storage');

        if (
            !$event->isMasterRequest() ||
            !$tokenStorage->getToken()
        ) {
            return false;
        }

        $token = $tokenStorage->getToken();

        if (!($token->getUser() instanceof User)) {
            return false;
        }

        $em = $this->container->get('doctrine.orm.entity_manager');
        $user = $token->getUser();
        $user->setLastActiveAt(new \DateTime());

        $em->persist($user);
        $em->flush();
    }
}
