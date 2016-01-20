# DunglasActionBundle: Symfony controllers, rethinked

[![Build Status](https://travis-ci.org/dunglas/DunglasActionBundle.svg?branch=master)](https://travis-ci.org/dunglas/DunglasActionBundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/7022bce4-9d67-4ade-9b19-cf7e417c0a80/mini.png)](https://insight.sensiolabs.com/projects/7022bce4-9d67-4ade-9b19-cf7e417c0a80)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dunglas/DunglasActionBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/dunglas/DunglasActionBundle/?branch=master)

This bundle is replacement for the standard Symfony controller system.

It is as convenient as the system shipped with the framework but doesn't have its drawbacks:

* Only one action per class thanks to the [`__invoke()`](http://php.net/manual/en/language.oop5.magic.php#object.invoke)
  method
* Action classes are automatically registered as services by the bundle
* Dependencies of action classes are **explicitly** injected in the constructor (no more ugly access to the service container)
* Dependencies of action classes are [autowired](https://dunglas.fr/2015/10/new-in-symfony-2-83-0-services-autowiring/)

It allows to create **reusable**, **framework agnostic** (especially when used with [the PSR-7 bridge](https://dunglas.fr/2015/06/using-psr-7-in-symfony/))
and **easy to unit test** actions.

See https://github.com/symfony/symfony/pull/16863#issuecomment-162221353 for the history behind this bundle.

## Installation

Use [Composer](http://getcomposer.org/) to install this bundle:

    composer require dunglas/action-bundle

Add the bundle in your application kernel:

```php
// app/AppKernel.php

public function registerBundles()
{
    return array(
        // ...
        new Dunglas\ActionBundle\DunglasActionBundle(),
        // ...
    );
}
```

Optional: to use the `@Route` annotation add the following lines in `app/config/routing.yml`:

```yaml
app:
    resource: "@AppBundle/Action/"
    type:     annotation
```

## Usage

1. Creates [an invokable class](http://www.lornajane.net/posts/2012/phps-magic-__invoke-method-and-the-callable-typehint)
   in the `Action` directory of your bundle:

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

    // The action is automatically registered as a service and dependencies are autowired
    public function __construct(RouterInterface $router, \Twig_Environment $twig)
    {
        $this->router = $router;
        $this->twig = $twig;
    }

    /**
     * @Route("/myaction")
     *
     * Using annotations are not mandatory, you can use XML or YAML routing file if you want.
     * If you want a framework agnostic action, don't use this annotation.
     */
    public function __invoke(Request $request)
    {
        if (!$this->request->isMethod('GET') {
            // Redirect in GET if the method is not POST
            return new RedirectResponse($this->router->generateUrl('my_action'), 301);
        }

        return new Response($this->twig->render('mytemplate.html.twig'));
    }
}
```

**There is not step 2! You're already done.**

All classes inside of the `Action` directory of your project bundles are automatically registered as services.
By convention, those services follow this pattern: `action.The\Fully\Qualified\Class\Name`.

For instance, the class in the example is automatically registered with the name `action.AppBundle\Action\MyAction`.

Thanks to the autowiring feature of the Symfony Dependency Injection component, you can just typehint dependencies
you need in the contructor, they will be automatically injected.

This bundle also hooks into the Routing Component (if it is available): when the `@Route` annotation is used like in the
example, the route is automatically registered (the bundle guesses the service to map with the given URL).

## Using the Symfony Micro Framework

You might be interested to see how this bundle can be used together with [the Symfony "Micro" framework](https://symfony.com/doc/current/cookbook/configuration/micro-kernel-trait.html).
Checkout this example:

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
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $c->loadFromExtension('framework', ['secret' => 'MySecretKey']);
    }
}
```

Amazing is'nt it?

## Using SensioFrameworkExtraBundle

**Caution**: why do you want to do that? If you don't know, use the standard annotation as previously explained!

If you want to use [the `@Route` annotation provided by SensioFrameworkExtraBundle](http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/routing.html),
add the following lines in `app/config/routing.yml`:

```yaml
app:
    resource: "@AppBundle/Action/"
    type:     annotation
```

Then, you adapt your action class like the following:

```php

// src/AppBundle/Action/MyAction.php

namespace AppBundle\Action;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route(service="action.AppBundle\Action\MyAction")
 *
 * The service name associated with the current action is automatically assigned by the bundle.
 * Unless with the standard annotation, when you use the one provided by SensioFrameworkExtraBundle,
 * you need to indicate this service name here.
 */
class MyAction
{
    private $router;
    private $twig;

    // The action is automatically registered as a service and dependencies are autowired
    public function __construct(RouterInterface $router, \Twig_Environment $twig)
    {
        $this->router = $router;
        $this->twig = $twig;
    }

    /**
     * @Route("/myaction")
     */
    public function __invoke(Request $request)
    {
        if (!$this->request->isMethod('GET') {
            // Redirect in GET if the method is not POST
            return new RedirectResponse($this->router->generateUrl('my_action'), 301);
        }

        return new Response($this->twig->render('mytemplate.html.twig'));
    }
}
```

## Credits

This bundle has been written by [Kévin Dunglas](https://dunglas.fr) and is sponsored by [Les-Tilleuls.coop](https://les-tilleuls.coop).
