<?php
/*
 * This file is part of the Trinity project.
 *
 */

namespace Trinity\Bundle\WidgetsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Trinity\Bundle\WidgetsBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     * @throws \RuntimeException
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('widgets');
        $rootNode
            ->children()
                ->arrayNode('cache')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enabled')->defaultTrue()->end()
                    ->scalarNode('cache_expiration_time')->defaultValue('PT10M')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
