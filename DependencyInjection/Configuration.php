<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\ActionBundle\DependencyInjection;

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
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('dunglas_action')
            ->children()
                ->arrayNode('autodiscover')
                    ->info('Autodiscover action classes stored in the configured directory of bundles and register them as service.')
                    ->canBeDisabled()
                    ->children()
                        ->scalarNode('directory')->defaultValue('Action')->info('The directory name to autodiscover in bundles.')->end()
                    ->end()
                ->end()
                ->arrayNode('directories')
                    ->info('List of directories relative to the kernel root directory containing action classes.')
                    ->prototype('scalar')->end()
                    ->defaultValue([])
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
