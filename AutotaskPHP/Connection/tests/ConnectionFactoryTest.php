<?php

namespace AutotaskPHP\Tests\Connection;

use AutotaskPHP\Connection\Connection;
use AutotaskPHP\Connection\ConnectionFactory;
use AutotaskPHP\Connection\Exceptions\MissingConnectionInformation;
use Http\Mock\Client;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class ConnectionFactoryTest extends TestCase
{
    public function test_it_cannot_make_connection_without_username(): void
    {
        $this->expectException(MissingConnectionInformation::class);
        $this->expectExceptionMessage('No username was specified.');

        ConnectionFactory::new()->make();
    }

    public function test_it_cannot_make_connection_without_password(): void
    {
        $this->expectException(MissingConnectionInformation::class);
        $this->expectExceptionMessage('No password was specified.');

        ConnectionFactory::new()
            ->username('test.user@autotask.net')
            ->make();
    }

    public function test_it_cannot_make_connection_without_integration_code(): void
    {
        $this->expectException(MissingConnectionInformation::class);
        $this->expectExceptionMessage('No integration code was specified.');

        ConnectionFactory::new()
            ->username('test.user@autotask.net')
            ->password('AbcXyz123')
            ->make();
    }

    public function test_it_cannot_make_connection_without_base_url(): void
    {
        $this->expectException(MissingConnectionInformation::class);
        $this->expectExceptionMessage('No base url was specified.');

        ConnectionFactory::new()
            ->username('test.user@autotask.net')
            ->password('AbcXyz123')
            ->integrationCode('ABCDEFG')
            ->make();
    }

    public function test_it_can_make_connection(): void
    {
        $connection = ConnectionFactory::new()
            ->username('test.user@autotask.net')
            ->password('AbcXyz123')
            ->integrationCode('ABCDEFG')
            ->baseUrl('https://autotask.net')
            ->make();

        $this->assertInstanceOf(Connection::class, $connection);

        $this->assertSame('test.user@autotask.net', $connection->configuration->username);
        $this->assertSame('AbcXyz123', $connection->configuration->password);
        $this->assertSame('ABCDEFG', $connection->configuration->integrationCode);
        $this->assertSame('https://autotask.net/', $connection->configuration->baseUrl);
    }

    public function test_it_can_make_connection_with_cache(): void
    {
        $cache = $this->getMockBuilder(CacheItemPoolInterface::class)->getMock();

        $connection = ConnectionFactory::new()
           ->username('test.user@autotask.net')
           ->password('AbcXyz123')
           ->integrationCode('ABCDEFG')
           ->baseUrl('https://autotask.net')
            ->cache($cache)
            ->cacheExpiresAfter(300)
            ->make();

        $this->assertSame($cache, $connection->configuration->cache);
        $this->assertSame(300, $connection->configuration->cacheExpiresAfter);
    }

    public function test_it_can_make_connection_with_client(): void
    {
        $client = new Client();

        $connection = ConnectionFactory::new()
           ->username('test.user@autotask.net')
           ->password('AbcXyz123')
           ->integrationCode('ABCDEFG')
           ->baseUrl('https://autotask.net')
           ->client($client)
           ->make();

        $connection->configuration->client->get('http://autotask.net');

        $lastRequest = $client->getLastRequest();

        $this->assertSame('http://autotask.net', $lastRequest->getUri()->__toString());
        $this->assertSame('GET', $lastRequest->getMethod());
    }

    public function test_it_can_make_connection_with_request_factory(): void
    {
        $requestFactory = $this->getMockBuilder(RequestFactoryInterface::class)->getMock();

        $connection = ConnectionFactory::new()
           ->username('test.user@autotask.net')
           ->password('AbcXyz123')
           ->integrationCode('ABCDEFG')
           ->baseUrl('https://autotask.net')
           ->requestFactory($requestFactory)
           ->make();

        $this->assertSame($requestFactory, $connection->configuration->requestFactory);
    }


    public function test_it_can_make_connection_with_stream_factory(): void
    {
        $streamFactory = $this->getMockBuilder(StreamFactoryInterface::class)->getMock();

        $connection = ConnectionFactory::new()
           ->username('test.user@autotask.net')
           ->password('AbcXyz123')
           ->integrationCode('ABCDEFG')
           ->baseUrl('https://autotask.net')
           ->streamFactory($streamFactory)
           ->make();

        $this->assertSame($streamFactory, $connection->configuration->streamFactory);
    }
}