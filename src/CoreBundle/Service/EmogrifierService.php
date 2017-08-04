<?php

namespace CoreBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Pelago\Emogrifier;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class EmogrifierService
{
    use ContainerAwareTrait;

    /**
     * @param string $twigTemplatePathOrContent
     * @param array twigTemplateData
     * @param bool isTwigTemplate
     */
    public function convert($twigTemplatePathOrContent, $twigTemplateData = [], $isTwigTemplate = true)
    {
        $emogrifier = new Emogrifier();

        $html = $isTwigTemplate
            ? $this->container->get('templating')->render($twigTemplatePathOrContent, $twigTemplateData)
            : $this->container->get('templating')->render(
                'CoreBundle::Emails/blank.html.twig',
                array_merge(
                    $twigTemplateData,
                    [
                        'content' => $twigTemplatePathOrContent,
                    ]
                )
            )
        ;

        $emogrifier->setHtml($html);

        return $emogrifier->emogrify();
    }
}
