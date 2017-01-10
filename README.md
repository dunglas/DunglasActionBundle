# DunglasActionBundle: Symfony controllers, redesigned

[![Build Status](https://travis-ci.org/dunglas/DunglasActionBundle.svg?branch=master)](https://travis-ci.org/dunglas/DunglasActionBundle)
[![Build status](https://ci.appveyor.com/api/projects/status/jpjsasx59syknghe?svg=true)](https://ci.appveyor.com/project/dunglas/dunglasactionbundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/7022bce4-9d67-4ade-9b19-cf7e417c0a80/mini.png)](https://insight.sensiolabs.com/projects/7022bce4-9d67-4ade-9b19-cf7e417c0a80)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dunglas/DunglasActionBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/dunglas/DunglasActionBundle/?branch=master)
[![StyleCI](https://styleci.io/repos/50048652/shield)](https://styleci.io/repos/50048652)

This bundle is a replacement for [the controller system](https://symfony.com/doc/current/book/controller.html) of the [Symfony framework](https://symfony.com) and for its [command system](https://symfony.com/doc/current/cookbook/console/console_command.html).

It is as convenient as the original but doesn't suffer from its drawbacks:

* Action and console classes are automatically **registered as services** by the bundle
* Their dependencies are **explicitly injected** in the constructor (no more ugly access to the service container) using the [autowiring feature of the Dependency Injection Component](https://dunglas.fr/2015/10/new-in-symfony-2-83-0-services-autowiring/)
* Only one action per class thanks to the [`__invoke()` method](http://php.net/manual/en/language.oop5.magic.php#object.invoke)
  (but you're still free to create classes with more than 1 action if you want to)
* 100% compatible with common libraries and bundles including [SensioFrameworkExtraBundle](https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/)
  annotations

DunglasActionBundle allows to create **reusable**, **framework agnostic** (especially when used with [the PSR-7 bridge](https://dunglas.fr/2015/06/using-psr-7-in-symfony/))
and **easy to unit test** classes.

See https://github.com/symfony/symfony/pull/16863#issuecomment-162221353 for the history behind this bundle.

## Installation

Use [Composer](https://getcomposer.org/) to install this bundle:

    composer require dunglas/action-bundle

Add the bundle in your application kernel:

```php
// app/AppKernel.php

public function registerBundles()
{
    return [
        // ...
        new Dunglas\ActionBundle\DunglasActionBundle(),
        // ...
    ];
}
```

Optional: to use the `@Route` annotation add the following lines in `app/config/routing.yml`:

```yaml
app:
    resource: '@AppBundle/Action/' # Use @AppBundle/Controller/ if you prefer
    type:     'annotation'
```

If you don't want to use annotations but prefer raw YAML, use the following syntax:

```yaml
foo:
    path:      /foo/{bar}
    defaults:  { _controller: 'AppBundle\Action\Homepage' } # this is the name of the autoregistered service corresponding to this action
```

## Usage

1. Create [an invokable class](http://www.lornajane.net/posts/2012/phps-magic-__invoke-method-and-the-callable-typehint)
   in the `Action\` namespace of your bundle:

```php

// src/AppBundle/Action/MyAction.php

namespace AppBundle\Action;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Homepage
{
    private $router;
    private $twig;

    /**
     * The action is automatically registered as a service and dependencies are autowired.
     * Typehint any service you need, it will be automatically injected.
     */
    public function __construct(RouterInterface $router, \Twig_Environment $twig)
    {
        $this->router = $router;
        $this->twig = $twig;
    }

    /**
     * @Route("/myaction", name="my_action")
     *
     * Using annotations is not mandatory, XML and YAML configuration files can be used instead.
     * If you want to decouple your actions from the framework, don't use annotations.
     */
    public function __invoke(Request $request)
    {
        if (!$request->isMethod('GET')) {
            // Redirect to the current URL using the the GET method if it's not the current one
            return new RedirectResponse($this->router->generateUrl('my_action'), 301);
        }

        return new Response($this->twig->render('mytemplate.html.twig'));
    }
}
```

Alternatively, you can create a typical Symfony controller class with several `*Action` methods in the `Controller` directory
of your bundle, it will be autowired the same way.

**There is no step 2! You're already done.**

All classes inside `Action/` and `Controller/` directories of your project bundles are automatically registered as services.
By convention, the service name is the Fully Qualified Name of the class.

For instance, the class in the example is automatically registered with the name `AppBundle\Action\Homepage`.

There are other classes/tags supported:

| Class Name               | Tag automatically added | Directory
| ------------------------ | ----------------------- | ---------
| Command                  | console.command         | Command
| EventSubscriberInterface | kernel.event_subscriber | EventSubscriber
| Twig_ExtensionInterface  | twig.extension          | Twig

Thanks to the [autowiring feature](http://symfony.com/blog/new-in-symfony-2-8-service-auto-wiring) of the Dependency Injection
Component, you can just typehint dependencies you need in the constructor, they will be automatically initialized and injected.

Service definition can easily be customized by explicitly defining a service named according to the same convention:

```yaml
# app/config/services.yml

services:
    # This is a custom service definition
    'AppBundle\Action\MyAction':
        class: 'AppBundle\Action\MyAction'
        arguments: [ '@router', '@twig' ]

    'AppBundle\Command\MyCommand':
        class: 'AppBundle\Command\MyCommand'
        arguments: [ '@router', '@twig' ]
        tags:
            - { name: console.command }

    'AppBundle\EventSubscriber\MySubscriber':
        class: 'AppBundle\EventSubscriber\MySubscriber'
        tags:
            - { name: kernel.event_subscriber }
```

This bundle also hooks into the Routing Component (if it is available): when the `@Route` annotation is used as in the example,
the route is automatically registered: the bundle guesses the service to map with the path specified in the annotation.

[Dive into the TestBundle](Tests/Fixtures/TestBundle) to discover more examples such as using custom services with ease
(no configuration at all) or classes containing several actions.

## Using the Symfony Micro Framework

You might be interested to see how this bundle can be used together with [the Symfony "Micro" framework](https://symfony.com/doc/current/cookbook/configuration/micro-kernel-trait.html).

Here we go:

```php
// MyMicroKernel.php

use AppBundle\Action\Homepage;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

final class MyMicroKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles()
    {
        return [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Dunglas\ActionBundle\DunglasActionBundle(),
            new AppBundle\AppBundle(),
        ];
    }

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        // Specify explicitly the controller
        $routes->add('/', Homepage::class, 'my_route');
        // Alternatively, use @Route annotations
        // $routes->import('@AppBundle/Action/', '/', 'annotation');
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $c->loadFromExtension('framework', ['secret' => 'MySecretKey']);
    }
}
```

Amazing isn't it?

Want to see a more advanced example? [Checkout our test micro kernel](Tests/Fixtures/TestKernel.php).

## Configuration

```yaml
# app/config/config.yml

dunglas_action:
    directories: # List of directories relative to the kernel root directory containing classes to auto-register.
        - '../src/*Bundle/{Controller,Action,Command,EventSubscriber}'
        # This one is not registered by default
        - '../src/*Bundle/My/Uncommon/Directory'
    tags:
        'Symfony\Component\Console\Command\Command': console.command
        'Symfony\Component\EventDispatcher\EventSubscriberInterface': kernel.event_subscriber
        'My\Custom\Interface\To\Auto\Tag':
            - 'my_custom.tag'
            - [ 'my_custom.tag_with_attributes', [ attribute: 'value' ] ]
```

## Credits

This bundle is brought to you by [Kévin Dunglas](https://dunglas.fr) and [awesome contributors](https://github.com/dunglas/DunglasActionBundle/graphs/contributors).
Sponsored by [Les-Tilleuls.coop](https://les-tilleuls.coop).
