<?php

namespace kissj\Event;

use kissj\AbstractController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EventController extends AbstractController
{
    public function __construct(
        private readonly EventRepository $eventRepository,
    ) {
    }

    public function list(Response $response): Response
    {
        return $this->view->render(
            $response,
            'event/landing.twig',
            [
                'events' => $this->eventRepository->findActiveEvents(),
                'allEvents' => $this->eventRepository->findAll(),
            ],
        );
    }

    public function landingPrettyUrl(string $eventSlug, Request $request, Response $response): Response
    {
        $event = $this->eventRepository->findBySlug($eventSlug);
        if ($event === null) {
            return $this->redirect($request, $response, 'eventList');
        }

        return $this->redirect($request, $response, 'getDashboard', ['eventSlug' => $event->slug]);
    }
}
