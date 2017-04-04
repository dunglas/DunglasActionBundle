<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\ActionBundle\Tests\Fixtures\AnonymousAction;

class AnAnonymousAction
{
    protected $anonymous;

    public function __construct()
    {
        $dummyService = null;
        $this->anonymous = new class($dummyService) {

            private $dummyService;

            public function __construct($dummyService)
            {
                $this->dummyService = $dummyService;
            }
        };
    }

    public function __invoke()
    {
        return 'Ho hi';
    }
}
