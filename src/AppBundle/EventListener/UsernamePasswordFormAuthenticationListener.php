<?php

namespace AppBundle\EventListener;

use Symfony\Component\Security\Http\Firewall\UsernamePasswordFormAuthenticationListener as BaseListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use AppBundle\Exception\BruteForceAttemptException;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UsernamePasswordFormAuthenticationListener extends BaseListener
{
    use ContainerAwareTrait;

    /**
     * @param Request $request
     */
    protected function attemptAuthentication(Request $request)
    {
        $bruteForceManager = $this->container->get('app.brute_force_manager');
        if (!$bruteForceManager->canLogin($request)) {
            throw new BruteForceAttemptException();
        }

        return parent::attemptAuthentication($request);
    }
}
