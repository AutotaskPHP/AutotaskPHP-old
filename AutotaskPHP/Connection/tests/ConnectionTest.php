<?php

namespace AutotaskPHP\Tests\Connection;

use AutotaskPHP\Connection\Connection;
use AutotaskPHP\Connection\ConnectionFactory;
use AutotaskPHP\Tests\Connection\Assertions\HttpRequest;
use Cache\Adapter\Common\CacheItem;
use Cache\Adapter\PHPArray\ArrayCachePool;
use Http\Mock\Client;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class ConnectionTest extends TestCase
{
    public function test_it_can_send_get_request(): void
    {
        $client = new Client();
        $connection = $this->createConnection($client);

        $response = $connection->get('Contacts');

        $this->assertInstanceOf(ResponseInterface::class, $response);

        HttpRequest::assert($client->getLastRequest())
           ->hasMethod('GET')
           ->hasUri('https://autotask.net/Contacts')
           ->hasHeaders([
               'Username' => 'test.user@example.com',
               'Secret' => 'Abc123',
               'APIIntegrationCode' => 'Xyz123',
           ]);
    }

    public function test_it_can_send_get_request_with_cache(): void
    {
        $client = new Client();
        $key = md5('https://autotask.net/:Tickets:' . serialize([]));
        $cache = $this->getMockBuilder(ArrayCachePool::class)->getMock();

        $cache
            ->expects(new InvokedCount(2))
            ->method('getItem')
            ->with($key)
            ->willReturn(new CacheItem($key));

        $connection = $this->createConnection($client, $cache);

        $response1 = $connection->get('Tickets');
        $response2 = $connection->get('Tickets');

        $this->assertInstanceOf(ResponseInterface::class, $response1);
        $this->assertInstanceOf(ResponseInterface::class, $response2);

        $this->assertCount(1, $client->getRequests());
    }

    public function test_it_can_refresh_cache_when_sending_get_request(): void
    {
        $client = new Client();
        $key = md5('https://autotask.net/:Tickets:' . serialize([]));
        $cache = new ArrayCachePool();

        $connection = $this->createConnection($client, $cache);

        $response1 = $connection->get('Tickets');
        $response2 = $connection->refreshCache(true)->get('Tickets');

        $this->assertInstanceOf(ResponseInterface::class, $response1);
        $this->assertInstanceOf(ResponseInterface::class, $response2);

        $this->assertCount(2, $client->getRequests());
        $this->assertEmpty($cache->getItems());
    }

    public function test_it_can_choose_not_to_cache_when_sending_get_request(): void
    {
        $client = new Client();
        $key = md5('https://autotask.net/:Tickets:' . serialize([]));
        $cache = new ArrayCachePool();

        $connection = $this->createConnection($client, $cache);

        $response1 = $connection->cache(false)->get('Tickets');
        $response2 = $connection->cache(false)->get('Tickets');
        $response3 = $connection->cache(false)->get('Tickets');

        $this->assertInstanceOf(ResponseInterface::class, $response1);
        $this->assertInstanceOf(ResponseInterface::class, $response2);
        $this->assertInstanceOf(ResponseInterface::class, $response3);

        $this->assertCount(3, $client->getRequests());
        $this->assertEmpty($cache->getItems());
    }

    public function test_it_can_send_head_request(): void
    {
        $client = new Client();
        $connection = $this->createConnection($client);

        $response = $connection->head('/');

        $this->assertInstanceOf(ResponseInterface::class, $response);

        HttpRequest::assert($client->getLastRequest())
           ->hasMethod('HEAD')
           ->hasUri('https://autotask.net/')
           ->hasHeaders([
               'Username' => 'test.user@example.com',
               'Secret' => 'Abc123',
               'APIIntegrationCode' => 'Xyz123',
           ]);
    }

    public function test_it_can_send_trace_request(): void
    {
        $client = new Client();
        $connection = $this->createConnection($client);

        $response = $connection->trace('/');

        $this->assertInstanceOf(ResponseInterface::class, $response);

        HttpRequest::assert($client->getLastRequest())
           ->hasMethod('TRACE')
           ->hasUri('https://autotask.net/')
           ->hasHeaders([
               'Username' => 'test.user@example.com',
               'Secret' => 'Abc123',
               'APIIntegrationCode' => 'Xyz123',
           ]);
    }

    public function test_it_can_send_post_request(): void
    {
        $client = new Client();
        $connection = $this->createConnection($client);

        $response = $connection->post('Tickets', [], json_encode([
            'data' => 'Hello'
        ]));

        $this->assertInstanceOf(ResponseInterface::class, $response);

        HttpRequest::assert($client->getLastRequest())
            ->hasMethod('POST')
            ->hasUri('https://autotask.net/Tickets')
            ->hasHeaders([
                'Username' => 'test.user@example.com',
                'Secret' => 'Abc123',
                'APIIntegrationCode' => 'Xyz123',
            ])
            ->hasBody('{"data":"Hello"}');
    }

    public function test_it_can_send_put_request(): void
    {
        $client = new Client();
        $connection = $this->createConnection($client);

        $response = $connection->put('Contacts/2', [], json_encode([
            'data' => [
                'firstName' => 'Aidan',
            ]
        ]));

        $this->assertInstanceOf(ResponseInterface::class, $response);

        HttpRequest::assert($client->getLastRequest())
           ->hasMethod('PUT')
           ->hasUri('https://autotask.net/Contacts/2')
           ->hasHeaders([
               'Username' => 'test.user@example.com',
               'Secret' => 'Abc123',
               'APIIntegrationCode' => 'Xyz123',
           ])
           ->hasBody('{"data":{"firstName":"Aidan"}}');
    }

    public function test_it_can_send_patch_request(): void
    {
        $client = new Client();
        $connection = $this->createConnection($client);

        $response = $connection->patch('Contacts/1', [], json_encode([
            'data' => [
                'firstName' => 'Aidan',
            ]
        ]));

        $this->assertInstanceOf(ResponseInterface::class, $response);

        HttpRequest::assert($client->getLastRequest())
           ->hasMethod('PATCH')
           ->hasUri('https://autotask.net/Contacts/1')
           ->hasHeaders([
               'Username' => 'test.user@example.com',
               'Secret' => 'Abc123',
               'APIIntegrationCode' => 'Xyz123',
           ])
           ->hasBody('{"data":{"firstName":"Aidan"}}');
    }

    public function test_it_can_send_delete_request(): void
    {
        $client = new Client();
        $connection = $this->createConnection($client);

        $response = $connection->delete('Contacts/1');

        $this->assertInstanceOf(ResponseInterface::class, $response);

        HttpRequest::assert($client->getLastRequest())
           ->hasMethod('DELETE')
           ->hasUri('https://autotask.net/Contacts/1')
           ->hasHeaders([
               'Username' => 'test.user@example.com',
               'Secret' => 'Abc123',
               'APIIntegrationCode' => 'Xyz123',
           ]);
    }

    public function test_it_can_send_options_request(): void
    {
        $client = new Client();
        $connection = $this->createConnection($client);

        $response = $connection->options('Contacts');

        $this->assertInstanceOf(ResponseInterface::class, $response);

        HttpRequest::assert($client->getLastRequest())
           ->hasMethod('OPTIONS')
           ->hasUri('https://autotask.net/Contacts')
           ->hasHeaders([
               'Username' => 'test.user@example.com',
               'Secret' => 'Abc123',
               'APIIntegrationCode' => 'Xyz123',
           ]);
    }

    private function createConnection(ClientInterface $client, ?CacheItemPoolInterface $cache = null): Connection
    {
        return ConnectionFactory::new()
            ->username('test.user@example.com')
            ->password('Abc123')
            ->integrationCode('Xyz123')
            ->baseUrl('https://autotask.net')
            ->client($client)
            ->cache($cache)
            ->make();
    }
}