<?php

namespace AppBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Exception\BruteForceAttemptException;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class BruteForceManager
{
    use ContainerAwareTrait;

    /**
     * @return bool
     */
    public function attemptAuthentication(Request $request)
    {
        $session = $this->container->get('session');
        $em = $this->container->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('AppBundle:UserLoginBlock');

        $userLoginBlock = $repository->getCurrentlyActive($request, $session);
        if ($userLoginBlock) {
            throw new BruteForceAttemptException(
                $this->container->get('translator')
                    ->trans(
                        'Your account has been blocked. It will be released at %time%',
                        [
                            '%time%' => $userLoginBlock->expiresAt()->format(DATE_ATOM),
                        ]
                    )
            );
        }

        return true;
    }
}
