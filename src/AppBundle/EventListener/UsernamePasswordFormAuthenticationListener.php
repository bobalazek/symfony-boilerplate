<?php

namespace AppBundle\EventListener;

use Symfony\Component\Security\Http\Firewall\UsernamePasswordFormAuthenticationListener as BaseListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

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
        $this->container->get('app.brute_force_manager')
            ->attemptAuthentication($request);

        return parent::attemptAuthentication($request);
    }
}
