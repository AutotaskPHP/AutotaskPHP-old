<?php

namespace AutotaskPHP\Connection;

use AutotaskPHP\Connection\Exceptions\MissingConnectionInformation;
use AutotaskPHP\Connection\Plugin\PluginCollection;
use Http\Client\Common\Plugin;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class ConnectionFactory
{
    private string $username;
    private string $password;
    private string $integrationCode;
    private string $baseUrl;
    private ?CacheItemPoolInterface $cache = null;
    private int $cacheExpiresAfter = 3306;
    private ClientInterface $client;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;
    private PluginCollection $plugins;

    public static function new(): self
    {
        return new self();
    }

    public function __construct()
    {
        $this->plugins = new PluginCollection();
    }

    public function username(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getUsername(): string
    {
        if (isset($this->username)) {
            return $this->username;
        }

        throw new MissingConnectionInformation(
            'No username was specified.'
        );
    }

    public function password(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPassword(): string
    {
        if (isset($this->password)) {
            return $this->password;
        }

        throw new MissingConnectionInformation(
            'No password was specified.'
        );
    }

    public function integrationCode(string $integrationCode): self
    {
        $this->integrationCode = $integrationCode;

        return $this;
    }

    public function getIntegrationCode(): string
    {
        if (isset($this->integrationCode)) {
            return $this->integrationCode;
        }

        throw new MissingConnectionInformation(
            'No integration code was specified.'
        );
    }

    public function baseUrl(string $url): self
    {
        $this->baseUrl = $url;

        return $this;
    }

    public function getBaseUrl(): string
    {
        if (isset($this->baseUrl)) {
            return $this->baseUrl;
        }

        throw new MissingConnectionInformation(
            'No base url was specified.'
        );
    }

    public function cache(?CacheItemPoolInterface $cache): self
    {
        $this->cache = $cache;

        return $this;
    }

    public function getCache(): ?CacheItemPoolInterface
    {
        return $this->cache;
    }

    public function cacheExpiresAfter(int $seconds): self
    {
        $this->cacheExpiresAfter = $seconds;

        return $this;
    }

    public function getCacheExpiresAfter(): int
    {
        return $this->cacheExpiresAfter;
    }

    public function client(ClientInterface $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getClient(): ClientInterface
    {
        return $this->client ?? Psr18ClientDiscovery::find();
    }

    public function requestFactory(RequestFactoryInterface $requestFactory): self
    {
        $this->requestFactory = $requestFactory;

        return $this;
    }

    public function getRequestFactory(): RequestFactoryInterface
    {
        return $this->requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
    }

    public function streamFactory(StreamFactoryInterface $streamFactory): self
    {
        $this->streamFactory = $streamFactory;

        return $this;
    }

    public function getStreamFactory(): StreamFactoryInterface
    {
        return $this->streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
    }

    public function addPlugin(Plugin $plugin): self
    {
        $this->plugins->add($plugin);

        return $this;
    }

    public function getPlugins(): PluginCollection
    {
        return $this->plugins;
    }

    public function make(): Connection
    {
        return new Connection(
            new Configuration(
                username: $this->getUsername(),
                password: $this->getPassword(),
                integrationCode: $this->getIntegrationCode(),
                baseUrl: $this->getBaseUrl(),
                cache: $this->getCache(),
                cacheExpiresAfter: $this->getCacheExpiresAfter(),
                client: $this->getClient(),
                plugins: $this->getPlugins(),
                requestFactory: $this->getRequestFactory(),
                streamFactory: $this->getStreamFactory()
            )
        );
    }
}