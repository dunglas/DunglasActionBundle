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

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
final class DummyAction
{
    private $dummy;

    public function __construct(DummyService $dummy)
    {
        $this->dummy = $dummy;
    }

    public function __invoke()
    {
        $this->dummy->doSomething();

        return new Response('Here we are!');
    }
}
