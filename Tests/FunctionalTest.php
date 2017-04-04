<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\ActionBundle\Tests;

use Dunglas\ActionBundle\Tests\Fixtures\AnonymousAction\AnAnonymousAction;
use Dunglas\ActionBundle\Tests\Fixtures\TestBundle\Twig\DummyExtension;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class FunctionalTest extends WebTestCase
{
    public function testDummyAction()
    {
        $client = static::createClient();

        static::$kernel->getContainer()->has('Dunglas\ActionBundle\Tests\Fixtures\TestBundle\Action\DummyAction');

        $crawler = $client->request('GET', '/');
        $this->assertSame('Here we are!', $crawler->text());
    }

    public function testSensioFrameworkExtraAction()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/extra');
        $this->assertSame('How are you?', $crawler->text());
    }

    public function testRouteAnnotationAction()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/annotation');
        $this->assertSame('Hey, ho, let\'s go!', $crawler->text());
    }

    public function testOverrideAction()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/override');
        $this->assertSame('Override', $crawler->text());
    }

    public function testMultiController()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/first');
        $this->assertSame('first', $crawler->text());

        $crawler = $client->request('GET', '/second');
        $this->assertSame('second', $crawler->text());
    }

    public function testLegacy()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/legacy');
        $this->assertSame('/isolated', $crawler->text());
    }

    public function testIsolated()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/isolated');
        $this->assertSame('Isolated.', $crawler->text());
    }

    public function testAbstractClassNotRegistered()
    {
        static::bootKernel();
        $this->assertFalse(static::$kernel->getContainer()->has('dunglas\actionBundle\tests\fixtures\testbundle\action\abstractaction'));
    }

    public function testAnonymousClassNotRegistered()
    {
        if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
            $hasFailed = null;
            try {
                static::bootKernel();
                $this->assertTrue(static::$kernel->getContainer()->has('dunglas\actionBundle\tests\fixtures\anonymousaction\ananonymousaction'));
                /** @var AnAnonymousAction $action */
                $action = static::$kernel->getContainer()->get('dunglas\actionBundle\tests\fixtures\anonymousaction\ananonymousaction');
                $this->assertEquals('Ho hi', $action());
                $hasFailed = false;
            } catch (\Symfony\Component\DependencyInjection\Exception\RuntimeException $e) {
                $hasFailed = true;
            }

            $this->assertFalse($hasFailed);
        } else {
            $this->markTestSkipped('PHP7+ is required to test anonymous classes');
        }
    }

    public function testCanAccessTraditionalController()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/traditional');
        $this->assertSame('traditional', $crawler->text());
    }

    public function testTwigExtension()
    {
        static::bootKernel();
        $this->assertTrue(static::$kernel->getContainer()->has(DummyExtension::class));
    }

    public function testSetterAutowiring()
    {
        if (!method_exists(Definition::class, 'setAutowiredMethods')) {
            $this->markTestSkipped('Setter autowiring requires Symfony 3.3+');
        }

        $client = static::createClient();

        $crawler = $client->request('GET', '/setter');
        $this->assertSame('setter', $crawler->text());
    }
}
