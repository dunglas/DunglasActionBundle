<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\ActionBundle\Tests\Fixtures\TestBundle\Action;

use Dunglas\ActionBundle\Tests\Fixtures\TestBundle\DummyService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OverrideAction
{
    public function __construct(DummyService $dummyService = null)
    {
        if (null !== $dummyService) {
            throw new \InvalidArgumentException('$dummyService must be null!');
        }
    }

    /**
     * @Route("/override")
     */
    public function __invoke()
    {
        return new Response('Override');
    }
}
