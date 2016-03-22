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
use Symfony\Component\Console\Command\Command;

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
            ->fixXmlConfig('directory', 'directories')
            ->children()
                ->arrayNode('directories')
                    ->info('List of directories relative to the kernel root directory containing classes.')
                    ->useAttributeAsKey('prefix')
                    ->prototype('array')
                        ->prototype('scalar')->end()
                    ->end()
                    ->defaultValue([
                        'controller' => ['../src/*Bundle/Controller', '../src/*Bundle/Action'],
                        'command' => ['../src/*Bundle/Command'],
                        'event_subscriber' => ['../src/*Bundle/EventSubscriber'],
                    ])
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
