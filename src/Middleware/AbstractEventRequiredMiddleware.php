<?php

declare(strict_types=1);

namespace kissj\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;

abstract class AbstractEventRequiredMiddleware extends BaseMiddleware
{
    abstract protected function createMissingEventResponse(Request $request): Response;

    public function process(Request $request, ResponseHandler $handler): Response
    {
        if ($this->tryGetEvent($request) === null) {
            return $this->createMissingEventResponse($request);
        }

        return $handler->handle($request);
    }
}
