<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\ActionBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class FunctionalTest extends WebTestCase
{
    public function testDummyAction()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');
        $this->assertSame('Here we are!', $crawler->text());
    }

    public function testSensioFrameworkExtraActionAction()
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
}
