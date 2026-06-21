<?php

declare(strict_types=1);

namespace kissj\Middleware;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Event\EventScope;
use kissj\Skautis\SkautisService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;
use Psr\Log\LoggerInterface;
use Slim\Routing\RouteContext;

class EventInfoMiddleware extends BaseMiddleware
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly EventScope $eventScope,
        private readonly SkautisService $skautisService,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        $eventSlug = $route?->getArgument('eventSlug') ?? '';
        $event = $this->eventRepository->findBySlug($eventSlug);
        $request = $request->withAttribute('event', $event);

        if ($event instanceof Event) {
            $this->eventScope->apply($event, $this->buildFullUrlLink($request, $event));

            if ($event->getEventType()->isLoginSkautisAllowed()) {
                $this->initSkautis($event);
            }
        }

        return $handler->handle($request);
    }

    private function buildFullUrlLink(Request $request, Event $event): string
    {
        return $this->getRouter($request)->fullUrlFor(
            $request->getUri(),
            'landingPrettyUrl',
            ['eventSlug' => $event->slug],
        );
    }

    private function initSkautis(Event $event): void
    {
        if ($event->skautisAppId === '') {
            $this->logger->error(
                sprintf('Event "%s" has Skautis login enabled, but skautisAppId in DB is empty', $event->slug),
            );

            return;
        }

        $this->skautisService->initSkautis($event->skautisAppId);
    }
}
