<?php

namespace Tests\TfaBundle\Controller;

use Tests\CoreBundle\WebTestCase;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class MyControllerTest extends WebTestCase
{
    public function testIfTfaSettingsPagesWork()
    {
        $routes = [
            'my.tfa',
            'my.tfa.authenticator',
            'my.tfa.recovery_codes',
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
