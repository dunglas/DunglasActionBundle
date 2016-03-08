<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\ActionBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * {@inheritdoc}
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class DunglasActionExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        foreach (['actions', 'commands'] as $type) {
            if (!$this->isConfigEnabled($container, $config[$type])) {
                continue;
            }

            $directories = $this->resolveDirectories($container, $config[$type]['directories']);

            $directories = array_merge($directories, $this->getAutodiscoveredDirectories(
                $container,
                $config[$type]['autodiscover']['directories']
            ));

            $container->setParameter(sprintf('dunglas_action.%s.directories', $type), $directories);
        }

        if ($this->isConfigEnabled($container, $config['actions']) && class_exists('Symfony\Component\Routing\Loader\AnnotationDirectoryLoader')) {
            $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
            $loader->load('routing.xml');
        }
    }

    /**
     * Gets the list of directories to autodiscover.
     *
     * @param ContainerBuilder $container
     * @param string           $directory
     *
     * @return array
     */
    private function resolveDirectories(ContainerBuilder $container, $directories) {
        $kernelRootDir = $container->getParameter('kernel.root_dir');

        foreach ($directories as &$directory) {
            $directory = $kernelRootDir.DIRECTORY_SEPARATOR.$directory;
        }

        return $directories;
    }

    /**
     * Gets the list of directories to autodiscover.
     *
     * @param ContainerBuilder $container
     * @param string[]         $directories
     *
     * @return string[]
     */
    private function getAutodiscoveredDirectories(ContainerBuilder $container, $directories)
    {
        $resolvedDirectories = [];

        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            $reflectionClass = new \ReflectionClass($bundle);
            $bundleDirectory = dirname($reflectionClass->getFileName());

            foreach ($directories as $discovery) {
                $actionDirectory = $bundleDirectory.DIRECTORY_SEPARATOR.$discovery;

                if (file_exists($actionDirectory)) {
                    $resolvedDirectories[] = $actionDirectory;
                }
            }
        }

        return $resolvedDirectories;
    }
}
