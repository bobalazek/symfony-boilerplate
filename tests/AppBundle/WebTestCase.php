<?php

namespace Tests\AppBundle;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyWebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Console\Input\StringInput;

class WebTestCase extends SymfonyWebTestCase
{
    protected $client;
    protected $application;

    public function setUp()
    {
        $this->client = static::createClient();

        // Update database schema in test database & load fixtures
        $this->runCommand('doctrine:schema:update --force');
        $this->runCommand('hautelook:fixtures:load --no-interaction');
    }

    protected function login($username = 'user@app.com')
    {
        $user = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:User')
            ->findByUsernameOrEmail($username)
        ;

        $session = $this->client->getContainer()->get('session');

        $firewall = 'main';

        $token = new UsernamePasswordToken(
            $user,
            $user->getPassword(),
            $firewall,
            $user->getRoles()
        );
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);

        return $this->client;
    }

    protected function runCommand($command)
    {
        $env = $this->client
            ->getKernel()
            ->getEnvironment();

        $command = sprintf('%s --quiet --env=%s', $command, $env);

        return $this->getApplication()->run(new StringInput($command));
    }

    protected function getApplication()
    {
        if (null === $this->application) {
            $this->application = new Application($this->client->getKernel());
            $this->application->setAutoExit(false);
        }

        return $this->application;
    }
}
