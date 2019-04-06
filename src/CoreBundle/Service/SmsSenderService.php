<?php

namespace CoreBundle\Service;

use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use CoreBundle\Exception\SmsSenderException;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class SmsSenderService
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

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
        $service = $this->container->getParameter('sms_sender_service');

        if (null === $service) {
            throw new SmsSenderException(
                'The SMS Service is not specified.'
            );
        }

        if ('twilio' === $service) {
            return $this->sendViaTwilio($to, $message);
        }

        if ('bobalazek_sms_sender' === $service) {
            return $this->sendViaBobalazekSmsSender($to, $message);
        }

        throw new SmsSenderException(
            'The SMS Service does not exist.'
        );
    }

    /**
     * @param string $to
     * @param string $message
     *
     * @return bool
     *
     * @throws SmsSenderException
     */
    public function sendViaTwilio($to, $message)
    {
        $sid = $this->container->getParameter('twilio_sms_sender_sid');
        $token = $this->container->getParameter('twilio_sms_sender_token');
        $from = $this->container->getParameter('twilio_sms_sender_from');

        try {
            $client = new \Twilio\Rest\Client($sid, $token);
            $message = $client->messages->create(
                $to,
                [
                    'from' => $from,
                    'body' => $message,
                ]
            );
        } catch (\Exception $e) {
            throw new SmsSenderException(
                'Something went wrong with Twilio. Exception: ' .
                $e->getMessage()
            );
        }
    }

    /**
     * @param string $to
     * @param string $message
     *
     * @return bool
     *
     * @throws SmsSenderException
     */
    public function sendViaBobalazekSmsSender($to, $message)
    {
        $url = $this->container->getParameter('bobalazek_sms_sender_url');
        $token = $this->container->getParameter('bobalazek_sms_sender_token');

        $client = new Client();
        try {
            $sendUrl = $url .
                '/api/send' .
                '?token=' . $token .
                '&to=' . $to .
                '&message=' . $message;
            $response = $client->request(
                'GET',
                $sendUrl,
                [
                    'timeout' => 5,
                ]
            );
        } catch (\Exception $e) {
            throw new SmsSenderException(
                'The SMS Service was not found. Exception: ' .
                $e->getMessage()
            );
        }

        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();
        $json = json_decode($body);

        if (null === $json) {
            throw new SmsSenderException(
                'The SMS Service did not give the correct response.'
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
