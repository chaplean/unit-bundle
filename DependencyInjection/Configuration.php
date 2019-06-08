<?php

namespace Chaplean\Bundle\UnitBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration.
 *
 * @package   Chaplean\Bundle\UnitBundle\DependencyInjection
 * @author    Tom - Chaplean <tom@chaplean.coop>
 * @copyright 2014 - 2019 Chaplean (https://www.chaplean.coop)
 * @since     1.0.0
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('chaplean_unit');

        $treeBuilder
            ->getRootNode()
            ->children()
                ->scalarNode('data_fixtures_namespace')
                    ->defaultValue('App\\')
                ->end()
                ->scalarNode('mocked_services')
                    ->defaultNull()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
