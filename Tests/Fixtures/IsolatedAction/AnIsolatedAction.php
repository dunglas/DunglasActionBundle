<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\ActionBundle\Tests\Fixtures\IsolatedAction;

use Symfony\Component\HttpFoundation\Response;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class AnIsolatedAction
{
    public function __invoke()
    {
        return new Response('Isolated.');
    }
}
