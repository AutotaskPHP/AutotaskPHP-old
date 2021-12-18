<?php

namespace AutotaskPHP\Tests\Connection\Assertions;

use PHPUnit\Framework\Assert;
use Psr\Http\Message\RequestInterface;

class HttpRequest
{
    public function __construct(private RequestInterface $request)
    {
        //
    }

    public static function assert(RequestInterface $request): static
    {
        return new static($request);
    }

    public function hasUri(string $uri): static
    {
        Assert::assertSame($uri, $this->request->getUri()->__toString());

        return $this;
    }

    public function hasMethod(string $method): static
    {
        Assert::assertSame(strtoupper($method), $this->request->getMethod());

        return $this;
    }

    public function hasBody(string $body): static
    {
        Assert::assertSame($body, $this->request->getBody()->__toString());

        return $this;
    }

    public function hasHeader(string $header, $value): static
    {
        $headerValue = $this->request->getHeaderLine($header);

        Assert::assertSame($value, $headerValue);

        return $this;
    }

    public function hasHeaders(array $headers): static
    {
        foreach ($headers as $header => $value) {
            $this->hasHeader($header, $value);
        }

        return $this;
    }
}