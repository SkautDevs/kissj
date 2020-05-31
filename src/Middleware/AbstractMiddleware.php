<?php

namespace kissj\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Routing\RouteContext;

abstract class AbstractMiddleware implements MiddlewareInterface {
    protected function getRouter(Request $request): RouteParserInterface {
        return RouteContext::fromRequest($request)->getRouteParser();
    }
}
