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
class SetterAction
{
    /**
     * @var DummyService
     */
    private $dummyService;

    public function setDummyService(DummyService $dummyService)
    {
        $this->dummyService = $dummyService;
    }

    public function __invoke()
    {
        $this->dummyService->doSomething();

        return new Response('setter');
    }
}
