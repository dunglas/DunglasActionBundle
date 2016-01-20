<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\ActionBundle\Routing;

use Symfony\Component\Routing\Loader\AnnotationClassLoader as BaseAnnotationClassLoader;
use Symfony\Component\Routing\Route;

/**
 * Sets the '_controller' default based on the service name associated with the action.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class AnnotationClassLoader extends BaseAnnotationClassLoader
{
    protected function configureRoute(Route $route, \ReflectionClass $class, \ReflectionMethod $method, $annot)
    {
        $route->setDefault('_controller', 'action.'.$class->getName().':'.$method->getName());
    }

    public function supports($resource, $type = null)
    {
        return parent::supports($resource, $type) && false !== strpos($resource, '\Action');
    }
}
