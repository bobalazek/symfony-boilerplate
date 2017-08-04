<?php

namespace Tests\CoreBundle\Controller;

use Tests\CoreBundle\WebTestCase;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class UserSystemControllerTest extends WebTestCase
{
    public function testPages()
    {
        $routes = [
            'login',
            'signup',
            'reset_password',
        ];

        foreach ($routes as $route) {
            $crawler = $this->client->request(
                'GET',
                $this->client->getContainer()
                    ->get('router')
                    ->generate($route)
            );

            $this->assertEquals(
                200,
                $this->client->getResponse()->getStatusCode(),
                'Something went wrong with the route "'.$route.'".'
            );
        }
    }
}
