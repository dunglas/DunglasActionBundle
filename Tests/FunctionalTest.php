<?php

namespace Dunglas\ActionBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FunctionalTest extends WebTestCase
{
    public function testDummyAction()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');
        $this->assertSame('Here we are!', $crawler->text());
    }
}
