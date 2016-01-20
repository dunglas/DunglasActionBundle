<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\ActionBundle\Routing;

use Symfony\Component\Routing\Loader\AnnotationFileLoader as BaseAnnotationFileLoader;

/**
 * Overrides the supported type.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class AnnotationFileLoader extends BaseAnnotationFileLoader
{
    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return 'action-annotation' === $type && parent::supports($resource);
    }
}
