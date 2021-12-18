<?php

namespace AutotaskPHP\Tests\Connection\Plugin;

use AutotaskPHP\Connection\Exceptions\InvalidPlugin;
use AutotaskPHP\Connection\Plugin\PluginCollection;
use Http\Client\Common\Plugin;
use PHPUnit\Framework\TestCase;

class PluginCollectionTest extends TestCase
{
    public function test_it_can_set_and_get_plugins(): void
    {
        $collection = new PluginCollection();

        $plugin = $this->getMockBuilder(Plugin::class)->getMock();

        $collection[0] = $plugin;

        $this->assertSame($plugin, $collection[0]);
    }

    public function test_it_cannot_set_and_get_non_plugins(): void
    {
        $this->expectException(InvalidPlugin::class);
        $this->expectExceptionMessage(
            sprintf('Expecting instance of [%s] received [string].', Plugin::class)
        );

        $collection = new PluginCollection();

        $collection[0] = 'Hello';
    }
}