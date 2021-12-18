<?php

namespace AutotaskPHP\Tests\Connection;

use AutotaskPHP\Connection\Configuration;
use AutotaskPHP\Connection\Plugin\PluginCollection;
use Http\Client\Common\HttpMethodsClientInterface;
use Http\Mock\Client;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class ConfigurationTest extends TestCase
{
    public function test_it_creates_client_correctly(): void
    {
        $client = new Client();
        $pluginCollection = new PluginCollection();
        $requestFactory = $this->getMockBuilder(RequestFactoryInterface::class)->getMock();
        $streamFactory = $this->getMockBuilder(StreamFactoryInterface::class)->getMock();

        $configuration = new Configuration(
            username: 'test.user@example.com',
            password: 'Abc123',
            integrationCode: 'Xyz123',
            baseUrl: 'https://autotask.net',
            cache: null,
            cacheExpiresAfter: 123,
            client: $client,
            plugins: $pluginCollection,
            requestFactory: $requestFactory,
            streamFactory: $streamFactory
        );

        $this->assertSame('test.user@example.com', $configuration->username);
        $this->assertSame('Abc123', $configuration->password);
        $this->assertSame('Xyz123', $configuration->integrationCode);
        $this->assertSame('https://autotask.net/', $configuration->baseUrl);
        $this->assertSame(null, $configuration->cache);
        $this->assertSame(123, $configuration->cacheExpiresAfter);
        $this->assertSame($requestFactory, $configuration->requestFactory);
        $this->assertSame($streamFactory, $configuration->streamFactory);

        $this->assertInstanceOf(HttpMethodsClientInterface::class, $configuration->client);
    }
}