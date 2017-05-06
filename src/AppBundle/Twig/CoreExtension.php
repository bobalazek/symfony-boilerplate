<?php

namespace AppBundle\Twig;

use Twig_Extension;
use Twig_SimpleFunction;
use Jenssegers\Agent\Agent;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class CoreExtension extends Twig_Extension
{
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction(
                'file_get_contents',
                array(
                    $this,
                    'fileGetContents',
                ),
                array(
                    'is_safe' => array('html'),
                )
            ),
            new Twig_SimpleFunction(
                'user_agent',
                array(
                    $this,
                    'userAgent',
                ),
                array(
                    'is_safe' => array('html'),
                )
            ),
        );
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

    public function getName()
    {
        return 'app_extension';
    }
}
