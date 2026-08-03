<?php

declare(strict_types=1);

namespace kissj\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;

class AddCorsHeaderForAppDomainsMiddleware extends BaseMiddleware
{
    public const array DEFAULT_ALLOWED_ORIGINS = ['https://entry.skauting.cz', 'https://kissj.skauting.cz'];

    /**
     * @param list<string> $allowedOrigins
     */
    public function __construct(
        private readonly array $allowedOrigins,
        private readonly ResponseFactoryInterface $responseFactory,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        if ($request->getMethod() === 'OPTIONS') { // handle preflight
            $response = $this->responseFactory->createResponse();
        } else {
            $response = $handler->handle($request);
        }

        $origin = $request->getHeaderLine('Origin');
        if (in_array($origin, $this->allowedOrigins, true)) {
            $response = $response->withHeader('Access-Control-Allow-Origin', $origin);
        }

        // no Allow-Credentials: routes carrying this middleware authenticate via API key, never cookies,
        // and allowlisted origin + credentials would be an unsafe combination
        return $response->withAddedHeader('Vary', 'Origin')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', ['authorization', 'Allow-Health', 'Content-Type'])
            ->withHeader('Access-Control-Max-Age', '3600');
    }
}
