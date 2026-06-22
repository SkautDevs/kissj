<?php

declare(strict_types=1);

namespace kissj\Telemetry\Sentry;

use kissj\Middleware\BaseMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;
use Sentry\State\Hub;
use Sentry\State\Scope;

class HttpContextMiddleware extends BaseMiddleware
{
    public function __construct(
        private readonly Hub $hub,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $serverParams = $request->getServerParams();
        $this->hub->configureScope(function (Scope $scope) use ($request, $serverParams): void {
            $scope->setContext('http', [
                'url' => (string)$request->getUri(),
                'ip' => $serverParams['REMOTE_ADDR'] ?? '',
                'http_method' => $request->getMethod(),
                'user_agent' => $request->getHeaderLine('User-Agent'),
            ]);
        });

        return $handler->handle($request);
    }
}
