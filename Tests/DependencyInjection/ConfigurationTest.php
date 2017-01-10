<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\ActionBundle\Tests\DependencyInjection;

use Dunglas\ActionBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

/**
 * @author Guilhem N <egetick@gmail.com>
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testTagsNormalization()
    {
        $processor = new Processor();

        // 'console.command' conversion
        $config = $processor->processConfiguration(new Configuration(true), [[
            'tags' => [__CLASS__ => 'console.command'],
        ]]);
        $this->assertEquals(
            [__CLASS__ => [['console.command', []]]],
            $config['tags']
        );

        // ['console.command'] conversion
        $config = $processor->processConfiguration(new Configuration(true), [[
            'tags' => [__CLASS__ => [['console.command', []], 'kernel.event_subscriber']],
        ]]);
        $this->assertEquals(
            [__CLASS__ => [
                ['console.command', []],
                ['kernel.event_subscriber', []],
            ]],
            $config['tags']
        );
    }

    public function testValidTag()
    {
        $processor = new Processor();

        $tags = [__CLASS__ => [['console.command', []]]];
        $config = $processor->processConfiguration(new Configuration(true), [['tags' => $tags]]);
        $this->assertEquals($tags, $config['tags']);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid tag format. They must be as following: ['my_tag.name', ['attribute' => 'value']]
     */
    public function testInvalidTag()
    {
        $processor = new Processor();

        $processor->processConfiguration(new Configuration(true), [[
            'tags' => [__CLASS__ => [['console.command', 'invalid']]],
        ]]);
    }
}
