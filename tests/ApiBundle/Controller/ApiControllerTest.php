<?php

namespace Tests\ApiBundle\Controller;

use Tests\CoreBundle\WebTestCase;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class ApiControllerTest extends WebTestCase
{
    public function testIfDefaultApiPageWorks()
    {
        $crawler = $this->client->request(
            'GET',
            $this->client->getContainer()
                ->get('router')
                ->generate('api')
        );

        $this->assertEquals(
            200,
            $this->client->getResponse()->getStatusCode()
        );
    }
}
