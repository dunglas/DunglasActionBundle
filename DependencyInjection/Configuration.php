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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
                ->arrayNode('methods')
                    ->info('The list of methods to autowire.')
                    ->prototype('scalar')->end()
                    ->defaultValue(['__construct', 'get*', 'set*'])
                ->end()
                ->arrayNode('directories')
                    ->info('List of directories relative to the kernel root directory containing classes.')
                    ->prototype('scalar')->end()
                    ->defaultValue(['../src/*Bundle/{Action,Command,Controller,EventSubscriber,Twig}'])
                ->end()
                ->arrayNode('tags')
                    ->info('List of tags to add when implementing the corresponding class.')
                    ->useAttributeAsKey('class')
                    ->prototype('array')
                        // Converts 'console.command' to ['console.command']
                        ->beforeNormalization()->ifString()->then(function ($v) {
                            return [$v];
                        })->end()
                        ->prototype('array')
                            // Converts 'console.command' to ['console.command', []]
                            ->beforeNormalization()->ifString()->then(function ($v) {
                                return [$v, []];
                            })->end()
                            ->validate()
                                ->ifTrue(function ($v) {
                                    return count($v) !== 2 || !is_string($v[0]) || !is_array($v[1]);
                                })
                                ->thenInvalid('Invalid tag format. They must be as following: [\'my_tag.name\', [\'attribute\' => \'value\']]')
                            ->end()
                            ->prototype('variable')->end()
                        ->end()
                    ->end()
                    ->defaultValue([
                        Command::class => [['console.command', []]],
                        EventSubscriberInterface::class => [['kernel.event_subscriber', []]],
                        \Twig_ExtensionInterface::class => [['twig.extension', []]],
                    ])
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
