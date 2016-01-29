# DunglasActionBundle: Symfony controllers, redesigned

[![Build Status](https://travis-ci.org/dunglas/DunglasActionBundle.svg?branch=master)](https://travis-ci.org/dunglas/DunglasActionBundle)
[![Build status](https://ci.appveyor.com/api/projects/status/jpjsasx59syknghe?svg=true)](https://ci.appveyor.com/project/dunglas/dunglasactionbundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/7022bce4-9d67-4ade-9b19-cf7e417c0a80/mini.png)](https://insight.sensiolabs.com/projects/7022bce4-9d67-4ade-9b19-cf7e417c0a80)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dunglas/DunglasActionBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/dunglas/DunglasActionBundle/?branch=master)
[![StyleCI](https://styleci.io/repos/50048652/shield)](https://styleci.io/repos/50048652)

This bundle is a replacement for [the controller system](https://symfony.com/doc/current/book/controller.html) of the [Symfony framework](https://symfony.com).

It is as convenient as the original but doesn't suffer from its drawbacks:

* Action classes are automatically **registered as services** by the bundle
* Dependencies of action classes are **explicitly injected** in the constructor (no more ugly access to the service container)
* Dependencies of action classes are **automatically injected** using the [autowiring feature of the Dependency Injection Component](https://dunglas.fr/2015/10/new-in-symfony-2-83-0-services-autowiring/)
* Only one action per class thanks to the [`__invoke()` method](http://php.net/manual/en/language.oop5.magic.php#object.invoke)
  (but you're still free to create classes with more than 1 action if you want to)
* 100% compatible with common libraries and bundles including [SensioFrameworkExtraBundle](https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/)
  annotations

DunglasActionBundle allows to create **reusable**, **framework agnostic** (especially when used with [the PSR-7 bridge](https://dunglas.fr/2015/06/using-psr-7-in-symfony/))
and **easy to unit test** actions.

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
app_action:
    resource: '@AppBundle/Action/'
    type:     'action-annotation'
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

class MyAction
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
        if (!$request->isMethod('GET') {
            // Redirect to the current URL using the the GET method if it's not the current one
            return new RedirectResponse($this->router->generateUrl('my_action'), 301);
        }

        return new Response($this->twig->render('mytemplate.html.twig'));
    }
}
```

**There is no step 2! You're already done.**

All classes inside of the `Action/` directory of your project bundles are automatically registered as services.
By convention, those services follow this pattern: `action.The\Fully\Qualified\Class\Name`.

For instance, the class in the example is automatically registered with the name `action.AppBundle\Action\MyAction`.

Thanks to the [autowiring feature](http://symfony.com/blog/new-in-symfony-2-8-service-auto-wiring) of the Dependency Injection
Component, you can just typehint dependencies you need in the constructor, they will be automatically injected.

Service definition can easily be customized by explicitly defining a service named according to the same convention:

```yaml
# app/config/services.yml

services:
    # This is a custom service definition
    'action.AppBundle\Action\MyAction':
        class: 'AppBundle\Action\MyAction'
        arguments: [ '@router', '@twig' ]
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
        $routes->add('/', 'action.AppBundle\Action\MyAction', 'my_route');
        // Alternatively, use @Route annotations
        // $routes->import('@AppBundle/Action/', '/', 'action-annotation');
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
    autodiscover:         # Autodiscover action classes stored in the configured directory of bundles and register them as service.
        enabled:   true
        directory: Action # The directory name to autodiscover in bundles.
    directories:   []     # List of directories relative to the kernel root directory containing action classes.
```

## Credits

This bundle is brought to you by [Kévin Dunglas](https://dunglas.fr) and is sponsored by [Les-Tilleuls.coop](https://les-tilleuls.coop).
