<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Middleware\AddCorsHeaderForAppDomainsMiddleware;
use Tests\AppTestCase;

class AddCorsHeaderForAppDomainsMiddlewareTest extends AppTestCase
{
    public function testEchoesRequestOrigin(): void
    {
        $app = $this->getTestApp();
        $middleware = $this->getService($app, AddCorsHeaderForAppDomainsMiddleware::class);
        $handler = new RequestHandlerSpy();
        $request = $this->createRequest('/v3/entry/code/abc', 'POST')
            ->withHeader('Origin', 'https://entry.example.org');

        $response = $middleware->process($request, $handler);

        self::assertTrue($handler->called);
        self::assertSame('https://entry.example.org', $response->getHeaderLine('Access-Control-Allow-Origin'));
        self::assertSame('Origin', $response->getHeaderLine('Vary'));
        self::assertStringContainsStringIgnoringCase('content-type', $response->getHeaderLine('Access-Control-Allow-Headers'));
        self::assertSame('3600', $response->getHeaderLine('Access-Control-Max-Age'));
        self::assertFalse($response->hasHeader('Access-Control-Allow-Credentials'));
    }

    public function testFallsBackToProductionOriginWithoutOriginHeader(): void
    {
        $app = $this->getTestApp();
        $middleware = $this->getService($app, AddCorsHeaderForAppDomainsMiddleware::class);
        $handler = new RequestHandlerSpy();
        $request = $this->createRequest('/v3/entry/code/abc', 'POST');

        $response = $middleware->process($request, $handler);

        self::assertSame('https://kissj.skauting.cz', $response->getHeaderLine('Access-Control-Allow-Origin'));
    }

    public function testOptionsPreflightShortCircuits(): void
    {
        $app = $this->getTestApp();
        $middleware = $this->getService($app, AddCorsHeaderForAppDomainsMiddleware::class);
        $handler = new RequestHandlerSpy();
        $request = $this->createRequest('/v3/entry/code/abc', 'OPTIONS')
            ->withHeader('Origin', 'https://entry.example.org');

        $response = $middleware->process($request, $handler);

        self::assertFalse($handler->called);
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('https://entry.example.org', $response->getHeaderLine('Access-Control-Allow-Origin'));
    }
}
