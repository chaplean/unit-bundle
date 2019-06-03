<?php

namespace Chaplean\Bundle\UnitBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class ChapleanUnitExtension.
 *
 * @package   Chaplean\Bundle\UnitBundle\DependencyInjection
 * @author    Tom - Chaplean <tom@chaplean.coop>
 * @copyright 2014 - 2019 Chaplean (https://www.chaplean.coop)
 * @since     1.0.0
 */
class ChapleanUnitExtension extends Extension
{
    /**
     * @param array                                                   $configs
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return void
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('chaplean_unit.data_fixtures_namespace', $config['data_fixtures_namespace']);
        $container->setParameter('chaplean_unit.mocked_services', $config['mocked_services']);
    }
}
