<?php

namespace kissj\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Interfaces\RouteParserInterface;
use Slim\Routing\RouteContext;

abstract class BaseMiddleware implements MiddlewareInterface {
    public function __invoke(Request $request, RequestHandler $handler): Response {
        return $this->process($request, $handler);
    }

    protected function getRouter(Request $request): RouteParserInterface {
        return RouteContext::fromRequest($request)->getRouteParser();
    }
}
