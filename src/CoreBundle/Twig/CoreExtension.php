<?php

namespace CoreBundle\Twig;

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

    /**
     * @return array
     */
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
            new Twig_SimpleFunction(
                'route_exists',
                [
                    $this,
                    'routeExists',
                ]
            ),
            new Twig_SimpleFunction(
                'bundle_exists',
                [
                    $this,
                    'bundleExists',
                ]
            ),
        ];
    }

    /**
     * @param string $filePath
     *
     * @return string
     */
    public function fileGetContents($filePath)
    {
        try {
            return file_get_contents($filePath);
        } catch (\Exception $e) {
            // try if the file exist relative to the web dir
            // (hack for emails inside console commands)
            if ($filePath[0] !== '/') {
                $filePath = dirname(__FILE__).'/../../../web/'.$filePath;
            }

            return file_get_contents($filePath);
        }
    }

    /**
     * @param string $userAgentString
     *
     * @return Agent
     */
    public function userAgent($userAgentString)
    {
        $agent = new Agent();
        $agent->setUserAgent($userAgentString);

        return $agent;
    }

    /**
     * @param string $ipAddress
     * @param string $type
     *
     * @return object|array
     */
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

    /**
     * @param mixed $data
     *
     * @return string
     */
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

    /**
     * Note: should NOT be used very often.
     *   Especially not on templates, that are
     *   parsed on every request!
     *
     * @param string $route
     *
     * @return bool
     */
    public function routeExists($route)
    {
        $router = $this->container->get('router');

        return $router->getRouteCollection()->get($route) === null
            ? false
            : true;
    }

    /**
     * @param string $bundle
     *
     * @return bool
     */
    public function bundleExists($bundle)
    {
        $bundles = $this->container->getParameter('kernel.bundles');

        return array_key_exists($bundle, $bundles);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'app_extension';
    }
}
