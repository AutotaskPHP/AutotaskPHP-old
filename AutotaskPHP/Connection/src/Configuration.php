<?php

namespace AutotaskPHP\Connection;

use AutotaskPHP\Connection\Plugin\PluginCollection;
use Http\Client\Common\HttpMethodsClient;
use Http\Client\Common\HttpMethodsClientInterface;
use Http\Client\Common\PluginClient;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Configuration
{
    public readonly string $username;
    public readonly string $password;
    public readonly string $integrationCode;
    public readonly string $baseUrl;
    public readonly ?CacheItemPoolInterface $cache;
    public readonly int $cacheExpiresAfter;
    public readonly HttpMethodsClientInterface $client;
    public readonly RequestFactoryInterface $requestFactory;
    public readonly StreamFactoryInterface $streamFactory;

    public function __construct(
        string $username,
        string $password,
        string $integrationCode,
        string $baseUrl,
        ?CacheItemPoolInterface $cache,
        int $cacheExpiresAfter,
        ClientInterface $client,
        PluginCollection $plugins,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->integrationCode = $integrationCode;
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
        $this->cache = $cache;
        $this->cacheExpiresAfter = $cacheExpiresAfter;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;

        $this->client = new HttpMethodsClient(
            new PluginClient($client, $plugins->toArray()),
            $requestFactory,
            $streamFactory
        );
    }
}