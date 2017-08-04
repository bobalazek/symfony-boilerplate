<?php

namespace CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use CoreBundle\DependencyInjection\Compiler\AuthenticationPass;

/**
 * @author Borut Balazek <bobalazek124@gmail.com>
 */
class CoreBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AuthenticationPass());
    }
}
