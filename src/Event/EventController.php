<?php

namespace kissj\Event;

use kissj\AbstractController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EventController extends AbstractController
{
    public function __construct(
        private readonly EventRepository $eventRepository,
    ) {}

    public function list(Response $response): Response
    {
        return $this->view->render(
            $response,
            'event/landing.twig',
            ['events' => $this->eventRepository->findActiveEvents()],
        );
    }
    /*
    public function createEvent(Request $request, Response $response, array $args) {
        $params = $request->getParams();
        /** @var \kissj\Event\EventService $eventService *//*
        $eventService = $this->eventService;
        if ($eventService->isEventDetailsValid(
            $params['slug'] ?? null,
            $params['readableName'] ?? null,
            $params['accountNumber'] ?? null,
            $params['automaticPaymentPairing'] ?? null,
            $params['prefixVariableSymbol'] ?? null,
            $params['bankId'] ?? null,
            $params['bankApi'] ?? null,
            $params['allowPatrols'] ?? null,
            $params['maximalClosedPatrolsCount'] ?? null,
            $params['minimalPatrolParticipantsCount'] ?? null,
            $params['maximalPatrolParticipantsCount'] ?? null,
            $params['allowIsts'] ?? null,
            $params['maximalClosedIstsCount'] ?? null)) {

            /** @var \kissj\Event\Event $newEvent *//*
            $newEvent = $eventService->createEvent(
                $params['slug'] ?? null,
                $params['readableName'] ?? null,
                $params['accountNumber'] ?? null,
                $params['prefixVariableSymbol'] ?? null,
                $params['automaticPaymentPairing'] ?? null,
                $params['bankId'] ?? null,
                $params['bankApi'] ?? null,
                $params['allowPatrols'] ?? null,
                $params['maximalClosedPatrolsCount'] ?? null,
                $params['minimalPatrolParticipantsCount'] ?? null,
                $params['maximalPatrolParticipantsCount'] ?? null,
                $params['allowIsts'] ?? null,
                $params['maximalClosedIstsCount'] ?? null);

            $this->flashMessages->success('Registrace je úspěšně vytvořená!');
            $this->logger->info('Created event with ID '.$newEvent->id.' and slug '.$newEvent->slug);

            return $response->withRedirect($this->router->urlFor('getDashboard',
                ['eventSlug' => $newEvent->slug]));
        }

        $this->flashMessages->warning('Některé údaje nebyly validní - prosím zkus zadání údajů znovu.');

        return $response->withRedirect($this->router->urlFor('createEvent'));
        // TODO add event-admins (roles table?)
    }*/
}
