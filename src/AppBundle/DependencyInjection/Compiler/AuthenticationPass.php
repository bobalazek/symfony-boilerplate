<?php

namespace AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class AuthenticationPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $container
            ->getDefinition('security.authentication.listener.form')
            ->setClass($container->getParameter('app.security.authentication.listener.form.class'))
            ->addMethodCall(
                'setContainer',
                [
                    new Reference('service_container'),
                ]
            );
    }
}
