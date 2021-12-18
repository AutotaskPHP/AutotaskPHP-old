<?php

namespace AutotaskPHP\Connection;

use Psr\Http\Message\ResponseInterface;

class Connection
{
    private bool $cacheRequests = true;
    private bool $refreshCache  = false;

    public function __construct(public readonly Configuration $configuration)
    {
        //
    }

    public function cache(bool $cache = true): static
    {
        $clone = clone $this;

        $clone->cacheRequests = $cache;

        return $clone;
    }

    public function refreshCache(bool $refresh = true): static
    {
        $clone = clone $this;

        $clone->refreshCache = $refresh;

        return $clone;
    }

    public function get(string $uri, array $headers = []): ResponseInterface
    {
        if ($this->isCacheable()) {
            return $this->performCachedGetRequest($uri, $headers);
        }

        return $this->performGetRequest($uri, $headers);
    }

    public function head(string $uri, array $headers = []): ResponseInterface
    {
        return $this->send('HEAD', $uri, $headers);
    }

    public function trace(string $uri, array $headers = []): ResponseInterface
    {
        return $this->send('TRACE', $uri, $headers);
    }

    public function post(string $uri, array $headers = [], $body = null): ResponseInterface
    {
        return $this->send('POST', $uri, $headers, $body);
    }

    public function put(string $uri, array $headers = [], $body = null): ResponseInterface
    {
        return $this->send('PUT', $uri, $headers, $body);
    }

    public function patch(string $uri, array $headers = [], $body = null): ResponseInterface
    {
        return $this->send('PATCH', $uri, $headers, $body);
    }

    public function delete(string $uri, array $headers = [], $body = null): ResponseInterface
    {
        return $this->send('DELETE', $uri, $headers, $body);
    }

    public function options(string $uri, array $headers = [], $body = null): ResponseInterface
    {
        return $this->send('OPTIONS', $uri, $headers, $body);
    }

    public function send(string $method, $uri, array $headers = [], $body = null): ResponseInterface
    {
        $headers = array_merge_recursive($headers, [
            'Username' => $this->configuration->username,
            'Secret' => $this->configuration->password,
            'APIIntegrationCode' => $this->configuration->integrationCode,
            'Content-Type' => 'application/json',
        ]);

        if ((str_starts_with($uri, 'http') || str_starts_with('https', $uri) === false)) {
            $uri = $this->configuration->baseUrl . trim($uri, '/');
        }

        return $this->configuration->client->send(
            $method, $uri, $headers, $body
        );
    }

    private function isCacheable(): bool
    {
        return $this->cacheRequests && ! is_null($this->configuration->cache);
    }

    private function performGetRequest(string $uri, array $headers): ResponseInterface
    {
        return $this->send('GET', $uri, $headers);
    }

    private function performCachedGetRequest(string $uri, array $headers): ResponseInterface
    {
        $uri = trim($uri, '/');
        $key = md5("{$this->configuration->baseUrl}:{$uri}:" . serialize($headers));

        // If we want to refresh the cache, remove it before we grab it.
        if ($this->refreshCache) {
            $this->configuration->cache->deleteItem($key);
        }

        $item = $this->configuration->cache->getItem($key);

        if (! $item->isHit()) {
            $response = $this->performGetRequest($uri, $headers);

            $item
                ->set([
                    'response' => $response,
                    'body' => $response->getBody()
                ])
                ->expiresAfter($this->configuration->cacheExpiresAfter);

            $this->configuration->cache->save($item);
        }

        $data = $item->get();

        /** @var ResponseInterface $response */
        $response = $data['response'];
        $stream = $this->configuration->streamFactory->createStream($data['body']);

        $stream->rewind();

        return $response->withBody($stream);
    }
}