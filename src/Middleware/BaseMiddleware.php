<?php

namespace kissj\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Routing\RouteContext;

abstract class BaseMiddleware implements MiddlewareInterface {
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        return $this->process($request, $handler);
    }

    protected function getRouter(Request $request): RouteParserInterface {
        return RouteContext::fromRequest($request)->getRouteParser();
    }
}
