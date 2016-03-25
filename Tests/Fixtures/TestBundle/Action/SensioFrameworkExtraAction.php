<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\ActionBundle\Tests\Fixtures\TestBundle\Action;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route(service="Dunglas\ActionBundle\Tests\Fixtures\TestBundle\Action\SensioFrameworkExtraAction")
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class SensioFrameworkExtraAction
{
    /**
     * @Route("/extra")
     */
    public function __invoke()
    {
        return new Response('How are you?');
    }
}
