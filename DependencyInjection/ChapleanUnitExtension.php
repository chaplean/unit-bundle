<?php

namespace Chaplean\Bundle\UnitBundle\DependencyInjection;

use Symfony\Component\ClassLoader\Psr4ClassLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ChapleanUnitExtension extends Extension
{
    /**
     * @param array                                                   $configs
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = array();
        foreach ($configs as $subConfig) {
            $config = array_merge($config, $subConfig);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        foreach ($config as $key => $value) {
            $container->setParameter($this->getAlias() . '.' . $key, $value);
        }

        $classLoader = new Psr4ClassLoader();
        $classLoader->addPrefix('Mockery\\', $container->getParameter('kernel.root_dir') . '/../vendor/mockery/mockery/library/');
        $classLoader->register();
    }
}
