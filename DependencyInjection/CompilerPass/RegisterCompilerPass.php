<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\ActionBundle\DependencyInjection\CompilerPass;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Finder\Finder;

/**
 * Automatically registers classes in the Action/ directory of bundles as a service.
 *
 * Autowiring is enabled for all registered actions. Those services are intended to be
 * used as controllers.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
final class RegisterCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $directories = [];
        $kernelRootDir = $container->getParameter('kernel.root_dir');

        foreach ($container->getParameter('dunglas_action.directories') as $prefix => $dirs) {
            $directories[$prefix] = [];
            foreach ($dirs as $directory) {
                $directories[$prefix][] = $kernelRootDir.DIRECTORY_SEPARATOR.$directory;
            }
        }

        foreach ($directories as $prefix => $dirs) {
            foreach ($dirs as $directory) {
                foreach ($this->getClasses($directory) as $class) {
                    $this->registerClass($container, $prefix, $class);
                }
            }
        }
    }

    /**
     * Gets the list of class names in the given directory.
     *
     * @param string $actionDirectory
     *
     * @return string[]
     */
    private function getClasses($actionDirectory)
    {
        $classes = [];
        $includedFiles = [];

        $finder = new Finder();
        try {
            $finder->in($actionDirectory)->files()->name('*.php');
        } catch (\InvalidArgumentException $e) {
            return [];
        }

        foreach ($finder as $file) {
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

        return array_keys($classes);
    }

    /**
     * Registers an action in the container.
     *
     * @param ContainerBuilder $container
     * @param string           $className
     */
    private function registerClass(ContainerBuilder $container, $prefix, $className)
    {
        $id = sprintf('%s.%s', $prefix, $className);

        if ($container->has($id)) {
            return;
        }

        $definition = $container->register($id, $className);
        $definition->setAutowired(true);

        $this->customizeServiceDefinition($definition);
    }

    private function customizeServiceDefinition(Definition $definition)
    {
        $class = $definition->getClass();

        // Commands
        if (is_subclass_of($class, Command::class)) {
            $definition->addTag('console.command');
        }

        // Event subscribers
        if (is_subclass_of($class, EventSubscriberInterface::class)) {
            $definition->addTag('kernel.event_subscriber');
        }
    }
}
