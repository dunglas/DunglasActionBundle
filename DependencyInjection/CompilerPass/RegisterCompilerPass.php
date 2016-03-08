<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\ActionBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
        $this->registerActions($container);
        $this->registerCommands($container);
    }

    private function registerActions(ContainerBuilder $container) {
        $directories = $container->getParameter('dunglas_action.actions.directories');

        foreach ($directories as $directory) {
            foreach ($this->getClasses($directory) as $class) {
                $this->registerClass($container, 'action', $class);
            }
        }
    }

    private function registerCommands(ContainerBuilder $container) {
        $directories = $container->getParameter('dunglas_action.commands.directories');

        foreach ($directories as $directory) {
            foreach ($this->getClasses($directory) as $class) {
                $this->registerClass($container, 'command', $class);
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

        $iterator = new \RegexIterator(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($actionDirectory, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            ),
            '/^.+\.php$/i',
            \RecursiveRegexIterator::GET_MATCH
        );

        foreach ($iterator as $file) {
            $sourceFile = $file[0];
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
    }
}
