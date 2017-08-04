<?php

namespace Tests\CoreBundle\Controller;

use Tests\CoreBundle\WebTestCase;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class MyControllerTest extends WebTestCase
{
    public function testPages()
    {
        $routes = [
            'my.settings',
            'my.password',
            'my.actions',
            'my.devices',
        ];

        $this->login('bobalazek124@gmail.com');

        foreach ($routes as $route) {
            // A hacky way to work around the KNP paginator bug
            //   (https://github.com/KnpLabs/knp-components/issues/90).
            unset($_GET['sort']);

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
