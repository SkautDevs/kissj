<?php

declare(strict_types=1);

namespace kissj\Middleware;

use kissj\Logging\Monolog\EventContextProcessor;
use kissj\Logging\Monolog\UserContextProcessor;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;

class MonologAdditionalContextMiddleware extends BaseMiddleware
{
    public function __construct(
        private readonly Logger $logger,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $this->logger->pushProcessor(
            new UserContextProcessor($this->tryGetUser($request))
        );

        $this->logger->pushProcessor(
            new EventContextProcessor($this->tryGetEvent($request)),
        );

        return $handler->handle($request);
    }
}
