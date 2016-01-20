# DunglasActionBundle: Symfony controllers, rethinked

This bundle is replacement for the standard Symfony controller system.

It is as convenient as the system shipped with the framework but doesn't have its drawbacks:

* Only one action per class thanks to the [`__invoke()`](http://php.net/manual/en/language.oop5.magic.php#object.invoke) method
* Action classes are automatically registered as services by the bundle
* Dependencies of action classes are **explicitly** injected in the constructor (no more ugly access to the service container)
* Dependencies of action classes are [autowired](https://dunglas.fr/2015/10/new-in-symfony-2-83-0-services-autowiring/)

It allows to create **reusable**, **framework agnostic** (especially when used with [the PSR-7 bridge](https://dunglas.fr/2015/06/using-psr-7-in-symfony/)) and **easy to unit test** actions.

See https://github.com/symfony/symfony/pull/16863#issuecomment-162221353 for the history behind this bundle.

## Usage

1. Creates [an invokable class](http://www.lornajane.net/posts/2012/phps-magic-__invoke-method-and-the-callable-typehint) in the `Action` directory of your bundle:

```php

// src/AppBundle/Action/MyAction.php

namespace AppBundle\Action;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The service name associated with the current action is automatically assigned by the bundle.
 * In a future version it will be possible to guess the service name.
 * It will allow to use the @Route annotation, without any parameter.
 *
 * Using annotations are not mandatory, you can use XML or YAML routing file if you want.
 *
 * @Route(service="action.AppBundle\Action\MyAction")
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

2. There is not step 2! You're already done.

All classes inside of the `Action` directory of your project bundles are automatically registered as services.
By convention, those services follow this pattern: `action.The\Fully\Qualified\Class\Name`.

For instance, the class in the example is automatically registered with the name `action.AppBundle\Action\MyAction`.

Thanks to the autowiring feature of the Symfony Dependency Injection component, you can just typehint dependencies
you need in the contructor, they will be automatically injected.

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

## TODO

* [ ] Allow to use `@Route` without specifying the service name

## Credits

This bundle has been written by [Kévin Dunglas](https://dunglas.fr) and is sponsored by [Les-Tilleuls.coop](https://les-tilleuls.coop).
