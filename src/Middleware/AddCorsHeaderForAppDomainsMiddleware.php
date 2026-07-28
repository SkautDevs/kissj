<?php

declare(strict_types=1);

namespace kissj\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;

class AddCorsHeaderForAppDomainsMiddleware extends BaseMiddleware
{
    public function process(Request $request, ResponseHandler $handler): Response
    {
        if ($request->getMethod() === 'OPTIONS') { // handle preflight
            $response = (new \Slim\Psr7\Response())->withStatus(200);
        } else {
            $response = $handler->handle($request);
        }

        $origin = $request->getHeaderLine('Origin');
        if ($origin === '') {
            $origin = 'https://kissj.skauting.cz';
        }

        // no Allow-Credentials: routes carrying this middleware authenticate via API key, never cookies,
        // and echoed origin + credentials would be an unsafe combination
        return $response->withHeader('Access-Control-Allow-Origin', $origin)
            ->withHeader('Vary', 'Origin')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', ['authorization', 'Allow-Health', 'Content-Type'])
            ->withHeader('Access-Control-Max-Age', '3600');
    }
}
