<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\ActionBundle;

use Dunglas\ActionBundle\DependencyInjection\CompilerPass\RegisterCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
final class DunglasActionBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterCompilerPass());

        if (!class_exists('Symfony\Component\Routing\Loader\AnnotationDirectoryLoader')) {
            return;
        }

        // Service names are similar to https://github.com/sensiolabs/SensioFrameworkExtraBundle/blob/master/Resources/config/routing.xml on purpose
        $annotDirDefinition = $container->register('dunglas_action.routing.loader.annot_dir', 'Symfony\Component\Routing\Loader\AnnotationDirectoryLoader');
        $annotDirDefinition->setPublic(false);
        $annotDirDefinition->setArguments([new Reference('file_locator'), new Reference('dunglas_action.routing.loader.annot_class')]);
        $annotDirDefinition->addTag('routing.loader');

        $annotFileDefinition = $container->register('dunglas_action.routing.loader.annot_file', 'Symfony\Component\Routing\Loader\AnnotationFileLoader');
        $annotFileDefinition->setPublic(false);
        $annotFileDefinition->setArguments([new Reference('file_locator'), new Reference('dunglas_action.routing.loader.annot_class')]);
        $annotFileDefinition->addTag('routing.loader');

        $annotClassDefinition = $container->register('dunglas_action.routing.loader.annot_class', 'Dunglas\ActionBundle\Routing\AnnotationClassLoader');
        $annotClassDefinition->setPublic(false);
        $annotClassDefinition->addArgument(new Reference('annotation_reader'));
        $annotClassDefinition->addTag('routing.loader');
    }
}
