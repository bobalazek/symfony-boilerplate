<?php

namespace Tests\CoreBundle\Controller;

use Tests\CoreBundle\WebTestCase;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserSystemControllerTest extends WebTestCase
{
    public function testIfLoginPageWorks()
    {
        $crawler = $this->client->request(
            'GET',
            $this->client->getContainer()
                ->get('router')
                ->generate('login')
        );
        $this->assertEquals(
            200,
            $this->client->getResponse()->isOk()
        );
    }

    public function testIfSignupPageWorks()
    {
        $crawler = $this->client->request(
            'GET',
            $this->client->getContainer()
                ->get('router')
                ->generate('signup')
        );
        $this->assertEquals(
            200,
            $this->client->getResponse()->getStatusCode()
        );
    }

    public function testIfResetPasswordPageWorks()
    {
        $crawler = $this->client->request(
            'GET',
            $this->client->getContainer()
                ->get('router')
                ->generate('reset_password')
        );
        $this->assertEquals(
            200,
            $this->client->getResponse()->getStatusCode()
        );
    }
}
