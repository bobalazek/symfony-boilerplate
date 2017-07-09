<?php

namespace AppBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class BruteForceManager
{
    use ContainerAwareTrait;

    /**
     * @return bool
     */
    public function canLogin(Request $request)
    {
        // TODO

        return true;
    }
}
