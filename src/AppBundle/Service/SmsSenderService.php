<?php

namespace AppBundle\Service;

use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use AppBundle\Exception\SmsSenderException;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class SmsSenderService
{
    use ContainerAwareTrait;

    /**
     * @param string $to
     * @param string $message
     *
     * @return bool
     *
     * @throws SmsSenderException
     */
    public function send($to, $message)
    {
        $to = preg_replace('/\s+/', '', $to); // trim all whitespace

        $url = $this->container->getParameter('sms_sender_url');
        $token = $this->container->getParameter('sms_sender_token');

        $client = new Client();
        try {
            $response = $client->request(
                'GET',
                $url.
                '/api/send'.
                '?token='.$token.
                '&to='.$to.
                '&message='.$message
            );
        } catch (\Exception $e) {
            throw new SmsSenderException(
                'The SMS Service was not found.'
            );
        }

        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();
        $json = json_decode($body);

        if ($json === null) {
            throw new SmsSenderException(
                'The SMS Service was not found.'
            );
        }

        if (isset($json->error)) {
            throw new SmsSenderException(
                $json->error->message
            );
        }

        return true;
    }
}
