<?php

namespace Tests\ApiBundle\Controller;

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
                ->generate('api.login')
        );
        $this->assertEquals(
            400, // because the username & password is missing
            $this->client->getResponse()->getStatusCode()
        );
    }

    public function testIfSignupPageWorks()
    {
        $crawler = $this->client->request(
            'GET',
            $this->client->getContainer()
                ->get('router')
                ->generate('api.signup')
        );
        $this->assertEquals(
            400, // because the registration data is missing
            $this->client->getResponse()->getStatusCode()
        );
    }

    public function testIfResetPasswordPageWorks()
    {
        $crawler = $this->client->request(
            'GET',
            $this->client->getContainer()
                ->get('router')
                ->generate('api.reset_password')
        );
        $this->assertEquals(
            400, // because the email
            $this->client->getResponse()->getStatusCode()
        );
    }
}
