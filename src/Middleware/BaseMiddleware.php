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
        if ($event = $this->tryGetEvent($request)) {
            $parameters['eventSlug'] = $event->slug;
        }
        $url = $this->getRouter($request)->urlFor($routeName, $parameters);

        return (new \Slim\Psr7\Response())->withHeader('Location', $url)->withStatus(302);
    }

    protected function tryGetEvent(Request $request): ?Event
    {
        /** @var Event|null $event */
        $event = $request->getAttribute('event');

        return $event;
    }

    protected function tryGetUser(Request $request): ?User
    {
        /** @var User|null $user */
        $user = $request->getAttribute('user');

        return $user;
    }

    protected function getUser(Request $request): User
    {
        /** @var User $user */
        $user = $request->getAttribute('user');

        return $user;
    }
}
