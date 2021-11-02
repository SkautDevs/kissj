<?php

namespace kissj\Middleware;

use kissj\Event\Event;
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

    private function getRouter(Request $request): RouteParserInterface
    {
        return RouteContext::fromRequest($request)->getRouteParser();
    }

    protected function createRedirectResponse(Request $request, string $routeName): Response
    {
        /** @var Event $event */ // TODO more strict
        $event = $request->getAttribute('event');
        $url = $this->getRouter($request)->urlFor($routeName, ['eventSlug' => $event->slug]);

        return (new \Slim\Psr7\Response())->withHeader('Location', $url)->withStatus(302);
    }
}
