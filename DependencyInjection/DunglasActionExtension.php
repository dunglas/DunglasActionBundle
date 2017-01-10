<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\ActionBundle\DependencyInjection;

use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Finder\Finder;
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
        $kernelRootDir = $container->getParameter('kernel.root_dir');

        $directoryList = [];
        foreach ($config['directories'] as $pattern) {
            list($classes, $directories) = $this->getClasses($this->getDirectory($kernelRootDir, $pattern));
            $directoryList = array_merge($directoryList, $directories);

            foreach ($classes as $class) {
                $this->registerClass($container, $class, $config['tags'], $config['methods']);
            }
        }

        $directories = [];
        foreach ($directoryList as $directory => $v) {
            $directory = realpath($directory);
            $container->addResource(new DirectoryResource($directory, '/\.php$/'));
            $directories[$directory] = true;
        }

        $container->setParameter('dunglas_action.directories', $directories);
    }

    /**
     * @param string $kernelRootDir
     * @param string $pattern
     *
     * @return string
     */
    private function getDirectory($kernelRootDir, $pattern)
    {
        $firstCharacter = substr($pattern, 0, 1);
        if ('/' === $firstCharacter || DIRECTORY_SEPARATOR === $firstCharacter) {
            return $pattern;
        }

        return $kernelRootDir.DIRECTORY_SEPARATOR.$pattern;
    }

    /**
     * Gets the list of class names in the given directory.
     *
     * @param string $directory
     *
     * @return array
     */
    private function getClasses($directory)
    {
        $classes = [];
        $directoryList = [];
        $includedFiles = [];

        $finder = new Finder();
        try {
            $finder->in($directory)->files()->name('*.php');
        } catch (\InvalidArgumentException $e) {
            return [[], []];
        }

        foreach ($finder as $file) {
            $directoryList[$file->getPath()] = true;
            $sourceFile = $file->getRealpath();
            if (!preg_match('(^phar:)i', $sourceFile)) {
                $sourceFile = realpath($sourceFile);
            }

            require_once $sourceFile;
            $includedFiles[$sourceFile] = true;
        }

        $declared = get_declared_classes();
        foreach ($declared as $className) {
            $reflectionClass = new \ReflectionClass($className);
            $sourceFile = $reflectionClass->getFileName();

            if ($reflectionClass->isAbstract()) {
                continue;
            }

            if (isset($includedFiles[$sourceFile])) {
                $classes[$className] = true;
            }
        }

        return [array_keys($classes), $directoryList];
    }

    /**
     * Registers an action in the container.
     *
     * @param ContainerBuilder $container
     * @param string           $className
     * @param array            $tags
     * @param string[]         $methods
     */
    private function registerClass(ContainerBuilder $container, $className, array $tags, array $methods)
    {
        if ($container->has($className)) {
            return;
        }

        $definition = $container->register($className, $className);
        if (method_exists($definition, 'setAutowiredMethods')) {
            $definition->setAutowiredMethods($methods);
        } else {
            $definition->setAutowired(true);
        }

        // Inject the container if applicable
        if (is_a($className, ContainerAwareInterface::class, true)) {
            $definition->addMethodCall('setContainer', [new Reference('service_container')]);
        }

        foreach ($tags as $tagClassName => $classTags) {
            if (!is_a($className, $tagClassName, true)) {
                continue;
            }

            foreach ($classTags as $classTag) {
                $definition->addTag($classTag[0], $classTag[1]);
            }
        }
    }
}
