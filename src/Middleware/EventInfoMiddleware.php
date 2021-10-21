<?php

declare(strict_types=1);

namespace kissj\Middleware;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

class EventInfoMiddleware extends BaseMiddleware
{
    public function __construct(
        private EventRepository $eventRepository,
        private Twig $view,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        $eventSlug = $route?->getArgument('eventSlug') ?? '';
        $event = $this->eventRepository->findOneBy(['slug' => $eventSlug]);
        if ($event instanceof Event) {
            $request = $request->withAttribute('event', $event);
            $this->view->getEnvironment()->addGlobal('event', $event); // used in templates
        } else {
            $request = $request->withAttribute('event', null);
        }

        return $handler->handle($request);
    }
}
