<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\ActionBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class CommandRegistrationTest extends KernelTestCase
{
    public function testCommandRegistrationAction()
    {
        static::bootKernel();
        $container = static::$kernel->getContainer();

        $commandId = 'command.dunglas\actionbundle\tests\fixtures\testbundle\command\foocommand';
        $this->assertTrue($container->has($commandId));
        $this->assertContains($commandId, $container->getParameter('console.command.ids'));

        $this->assertTrue($container->has('command.dunglas\actionbundle\tests\fixtures\testbundle\command\bar'));
    }
}
