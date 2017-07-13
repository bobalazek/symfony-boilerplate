<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

            // KNP
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new Knp\DoctrineBehaviors\Bundle\DoctrineBehaviorsBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),

            // CORS
            new Nelmio\CorsBundle\NelmioCorsBundle(),

            // JMS
            new JMS\SerializerBundle\JMSSerializerBundle(),

            // Doctrine migrations
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),

            // Assetic
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),

            // Doctrine Extensions
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),

            // GeoIP
            new Cravler\MaxMindGeoIpBundle\CravlerMaxMindGeoIpBundle(),

            // MISD
            new Misd\PhoneNumberBundle\MisdPhoneNumberBundle(),

            // Sonata
            new Sonata\CoreBundle\SonataCoreBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
            new Sonata\AdminBundle\SonataAdminBundle(),
            new Picoss\SonataExtraAdminBundle\PicossSonataExtraAdminBundle(),

            // App
            new AppBundle\AppBundle(),
            new ApiBundle\ApiBundle(),
            new AdminBundle\AdminBundle(),
            new DevBundle\DevBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();
            $bundles[] = new JMS\DiExtraBundle\JMSDiExtraBundle($this);
            $bundles[] = new JMS\AopBundle\JMSAopBundle();
            $bundles[] = new JMS\TranslationBundle\JMSTranslationBundle();
        }

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return dirname(__DIR__).'/var/cache/'.$this->getEnvironment();
    }

    public function getLogDir()
    {
        return dirname(__DIR__).'/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }
}
