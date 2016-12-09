<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Dunglas\ActionBundle\DunglasActionBundle;
use Dunglas\ActionBundle\Tests\Fixtures\IsolatedAction\AnIsolatedAction;
use Dunglas\ActionBundle\Tests\Fixtures\NotScannedBundle\NotScannedBundle;
use Dunglas\ActionBundle\Tests\Fixtures\TestBundle\Action\DummyAction;
use Dunglas\ActionBundle\Tests\Fixtures\TestBundle\Action\OverrideAction;
use Dunglas\ActionBundle\Tests\Fixtures\TestBundle\Action\SetterAction;
use Dunglas\ActionBundle\Tests\Fixtures\TestBundle\TestBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new DunglasActionBundle(),
            new SensioFrameworkExtraBundle(),
            new TestBundle(),
            new NotScannedBundle(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        // Specify explicitly the controller
        $routes->add('/', DummyAction::class, 'dummy');
        $routes->add('/isolated', AnIsolatedAction::class, 'isolated');
        $routes->add('/setter', SetterAction::class, 'setter');

        // Use the @Route annotation
        $routes->import('@TestBundle/Action/', '/', 'annotation');

        // Cohabitation between old school controllers and actions
        $routes->import('@TestBundle/Controller/', '/', 'annotation');
        $routes->import('@NotScannedBundle/Controller/', '/', 'annotation');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $c->loadFromExtension('framework', [
            'secret' => 'MySecretKey',
            'test' => null,
        ]);

        $c->loadFromExtension('dunglas_action', [
            'directories' => [
                '%kernel.root_dir%/TestBundle/Controller',
                'IsolatedAction',
                'NotExistingDirectory',
                '*Bundle/Action',
                '*Bundle/Command',
                '*Bundle/EventSubscriber',
                '*Bundle/Twig',
            ],
        ]);

        $c->register(OverrideAction::class, OverrideAction::class);
    }
}
