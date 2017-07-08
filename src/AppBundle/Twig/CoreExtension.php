<?php

namespace AppBundle\Twig;

use Twig_Extension;
use Twig_SimpleFunction;
use Jenssegers\Agent\Agent;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class CoreExtension extends Twig_Extension
{
    use ContainerAwareTrait;

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction(
                'file_get_contents',
                [
                    $this,
                    'fileGetContents',
                ],
                [
                    'is_safe' => ['html'],
                ]
            ),
            new Twig_SimpleFunction(
                'user_agent',
                [
                    $this,
                    'userAgent',
                ],
                [
                    'is_safe' => ['html'],
                ]
            ),
            new Twig_SimpleFunction(
                'geo_ip',
                [
                    $this,
                    'geoIp',
                ],
                [
                    'is_safe' => ['html'],
                ]
            ),
            new Twig_SimpleFunction(
                'dump_data',
                [
                    $this,
                    'dumpData',
                ],
                [
                    'is_safe' => ['html'],
                ]
            ),
        ];
    }

    public function fileGetContents($file)
    {
        try {
            return file_get_contents($file);
        } catch (\Exception $e) {
            // try if the file exist relative to the web dir
            // (hack for emails inside console commands)
            if ($file[0] !== '/') {
                $file = dirname(__FILE__).'/../../../web/'.$file;
            }

            return file_get_contents($file);
        }
    }

    public function userAgent($userAgentString)
    {
        $agent = new Agent();
        $agent->setUserAgent($userAgentString);

        return $agent;
    }

    public function geoIp($ipAddress = 'me', $type = 'city')
    {
        $geoIpService = $this->container->get('cravler_max_mind_geo_ip.service.geo_ip_service');

        try {
            return $geoIpService->getRecord($ipAddress, $type);
        } catch (\Exception $e) {
            return [
                'error' => $e,
            ];
        }
    }

    public function dumpData($data)
    {
        $html = '';

        if (is_object($data)) {
            $data = (array) $data;
        }

        if (
            !empty($data) &&
            is_array($data)
        ) {
            $html .= '<ul>';
            foreach ($data as $key => $val) {
                $html .= '<li>';
                $html .= '<b>'.$key.'</b>: ';
                if (is_array($val)) {
                    $html .= $this->dumpData($val);
                } else {
                    $html .= $val;
                }
                $html .= '</li>';
            }
            $html .= '</ul>';
        } elseif (!empty($data)) {
            $html .= $data;
        }

        return $html;
    }

    public function getName()
    {
        return 'app_extension';
    }
}
