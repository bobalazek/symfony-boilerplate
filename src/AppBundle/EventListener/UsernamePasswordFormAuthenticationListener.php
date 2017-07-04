<?php

namespace AppBundle\EventListener;

use AppBundle\Exception\Exception\BruteForceAttemptException;
use Symfony\Component\Security\Http\Firewall\UsernamePasswordFormAuthenticationListener as BaseListener;
use Symfony\Component\HttpFoundation\Request;

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
        $canLogin = true; // TODO

        if (!$canLogin) {
            throw new BruteForceAttemptException('Brute force attempt');
        }

        return parent::attemptAuthentication($request);
    }
}
