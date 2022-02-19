<?php

namespace kissj\Middleware;

use kissj\Event\Event;
use kissj\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Interfaces\RouteParserInterface;
use Slim\Routing\RouteContext;

abstract class BaseMiddleware implements MiddlewareInterface
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        return $this->process($request, $handler);
    }

    protected function getRouter(Request $request): RouteParserInterface
    {
        return RouteContext::fromRequest($request)->getRouteParser();
    }

    protected function createRedirectResponse(Request $request, string $routeName): Response
    {
        $parameters = [];
        if ($event = $this->getEvent($request)) {
            $parameters['eventSlug'] = $event->slug;
        }
        $url = $this->getRouter($request)->urlFor($routeName, $parameters);

        return (new \Slim\Psr7\Response())->withHeader('Location', $url)->withStatus(302);
    }

    protected function getEvent(Request $request): ?Event
    {
        return $request->getAttribute('event');
    }

    protected function getUser(Request $request): ?User
    {
        return $request->getAttribute('user');
    }
}
