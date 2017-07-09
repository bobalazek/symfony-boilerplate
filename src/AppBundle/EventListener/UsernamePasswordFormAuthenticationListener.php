<?php

namespace AppBundle\EventListener;

use Symfony\Component\Security\Http\Firewall\UsernamePasswordFormAuthenticationListener as BaseListener;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Manager\BruteForceManager;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UsernamePasswordFormAuthenticationListener extends BaseListener
{
    /**
     * @var BruteForceManager
     */
    protected $bruteForceManager;

    /**
     * @return BruteForceManager
     */
    public function getBruteForceManager()
    {
        return $this->bruteForceManager;
    }
    /**
     * @param BruteForceManager $bruteForceManager
     */
    public function setBruteForceManager(BruteForceManager $bruteForceManager)
    {
        $this->bruteForceManager = $bruteForceManager;
    }

    /**
     * @param Request $request
     */
    protected function attemptAuthentication(Request $request)
    {
        $this->bruteForceManager->attemptAuthentication($request);

        return parent::attemptAuthentication($request);
    }
}
