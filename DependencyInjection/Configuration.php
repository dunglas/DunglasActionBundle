<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\ActionBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * {@inheritdoc}
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $autodiscoverNode = new ArrayNodeDefinition('autodiscover');
        $autodiscoverNode
            ->info('Autodiscover classes stored in the configured directory of bundles and register them as service.')
            ->canBeDisabled()
            ->children()
                ->arrayNode('directories')
                    ->info('The directories name to autodiscover in bundles.')
                    ->prototype('scalar')->end()
                    ->defaultValue(['Action'])
                ->end()
            ->end();

        $directoriesNode = new ArrayNodeDefinition('directories');
        $directoriesNode
            ->info('List of directories relative to the kernel root directory containing action classes.')
            ->prototype('scalar')->end()
            ->defaultValue([]);

        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('dunglas_action')
            ->children()
                ->arrayNode('actions')
                    ->canBeDisabled()
                    ->append($autodiscoverNode)
                    ->append($directoriesNode)
                ->end()
                ->arrayNode('commands')
                    ->canBeDisabled()
                    ->append($autodiscoverNode)
                    ->append($directoriesNode)
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
