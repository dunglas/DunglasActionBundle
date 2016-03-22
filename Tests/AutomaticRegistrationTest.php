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
 * @author Guilhem N <egetick@gmail.com>
 */
class AutomaticRegistrationTest extends KernelTestCase
{
    public function testCommandRegistration()
    {
        static::bootKernel();
        $container = static::$kernel->getContainer();

        $commandId = 'command.dunglas\actionbundle\tests\fixtures\testbundle\command\foocommand';
        $this->assertTrue($container->has($commandId));
        $this->assertContains($commandId, $container->getParameter('console.command.ids'));

        $commandId = 'command.dunglas\actionbundle\tests\fixtures\testbundle\command\bar';
        $this->assertTrue($container->has($commandId));
        $this->assertNotContains($commandId, $container->getParameter('console.command.ids'));
    }

    public function testEventSubscriberRegistration()
    {
        static::bootKernel();
        $container = static::$kernel->getContainer();

        $listenerId = 'event_subscriber.dunglas\actionbundle\tests\fixtures\testbundle\eventsubscriber\mysubscriber';
        $this->assertTrue($container->has($listenerId));
    }
}
