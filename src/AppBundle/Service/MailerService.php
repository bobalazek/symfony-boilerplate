<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class MailerService
{
    use ContainerAwareTrait;

    protected $swiftMessageInstance;

    /**
     * Prepares the (swift) email and sends it.
     *
     * @param array $data        Swiftmailer data
     * @param array $attachments
     *
     * @return int Number of successfully sent emails
     *
     * @throws \Exception If subject or recipient (to) are not specified
     */
    public function swiftMessageInitializeAndSend(array $data, array $attachments = [])
    {
        $swiftMessageInstance = \Swift_Message::newInstance();

        if (!isset($data['subject'])) {
            throw new \Exception('You need to specify a subject.');
        }

        if (!isset($data['to'])) {
            throw new \Exception('You need to specify a recipient.');
        }

        $senderEmail = $this->container->getParameter('sender_address');
        $senderName = $this->container->getParameter('sender_name');
        $from = isset($data['from'])
            ? $data['from']
            : [$senderEmail => $senderName];
        $to = $data['to'];

        $swiftMessageInstance
            ->setSubject($data['subject'])
            ->setTo($to)
            ->setFrom($from)
        ;

        if (isset($data['reply_to'])) {
            $swiftMessageInstance->setReplyTo($data['reply_to']);
        }

        if (isset($data['cc'])) {
            $swiftMessageInstance->setCc($data['cc']);
        }

        if (isset($data['bcc'])) {
            $swiftMessageInstance->setBcc($data['bcc']);
        }

        $templateData = [
            'email' => $to,
            'swiftMessage' => $swiftMessageInstance,
        ];

        if ($this->container->get('security.token_storage')->getToken()) {
            $templateData['user'] = $this->container->get('security.token_storage')->getToken()->getUser();
        }

        if (isset($data['template_data'])) {
            $templateData = array_merge(
                $templateData,
                $data['template_data']
            );
        }

        if (isset($data['body'])) {
            $bodyType = isset($data['body_type'])
                ? $data['body_type']
                : 'text/html';
            $isTwigTemplate = isset($data['contentIsTwigTemplate'])
                ? $data['contentIsTwigTemplate']
                : true;

            $swiftMessageBody = $this->container->get('app.emogrifier')->convert(
                $data['body'],
                $templateData,
                $isTwigTemplate
            );

            $swiftMessageInstance->setBody($swiftMessageBody, $bodyType);
        }

        if (!empty($attachments)) {
            foreach ($attachments as $key => $attachment) {
                if (is_string($key)) {
                    $swiftMessageInstance->attach(
                        \Swift_Attachment::fromPath($key)
                            ->setFilename($attachment)
                    );
                } else {
                    $swiftMessageInstance->attach(
                        \Swift_Attachment::fromPath($attachment)
                    );
                }
            }
        }

        return $this->container->get('mailer')->send($swiftMessageInstance);
    }

    /***** Swift Message Instance *****/

    /**
     * @return \Swift_Message
     */
    public function getSwiftMessageInstance()
    {
        return $this->swiftMessageInstance;
    }

    /**
     * @param \Swift_Message $swiftMessageInstance
     *
     * @return MailerService
     */
    public function setSwiftMessageInstance(\Swift_Message $swiftMessageInstance)
    {
        $this->swiftMessageInstance = $swiftMessageInstance;

        return $this;
    }

    /**
     * Sends the (swift) email.
     *
     * @param mixed $swiftMessage
     */
    public function send($swiftMessage = false)
    {
        if (!$swiftMessage) {
            $swiftMessage = $this->getSwiftMessageInstance();
        }

        return $this->container->get('mailer')->send($swiftMessage);
    }

    /**
     * Short for swift image.
     *
     * @param string $path
     */
    public function image($path)
    {
        return \Swift_Image::fromPath($path);
    }
}
