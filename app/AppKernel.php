<?php

// @codingStandardsIgnoreFile

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * Class AppKernel.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.coop)
 * @since     1.0.0
 */
class AppKernel extends Kernel
{
    /**
     * @return array
     */
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Chaplean\Bundle\UnitBundle\ChapleanUnitBundle(),
            new Liip\FunctionalTestBundle\LiipFunctionalTestBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Chaplean\Bundle\MailerBundle\ChapleanMailerBundle(),
        );

        return $bundles;
    }

    /**
     * @return string
     */
    public function getRootDir()
    {
        return __DIR__;
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return dirname(__DIR__) . '/var/cache/' . $this->getEnvironment();
    }

    /**
     * @return string
     */
    public function getLogDir()
    {
        return dirname(__DIR__) . '/var/logs';
    }

    /**
     * @param LoaderInterface $loader Resource loader.
     *
     * @return void
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $configType = $_SERVER['CONFIG_TYPE'] ?? null;

        if (!in_array($configType, array('default', 'sqlite'))) {
            $configType = 'default';
        }

        $loader->load($this->getRootDir() . '/config/' . $configType . '/config.yml');
    }
}
