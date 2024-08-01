<?php

declare(strict_types=1);

namespace kissj\Deal;

use kissj\AbstractController;
use kissj\Participant\ParticipantRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use kissj\Event\Event;

class DealController extends AbstractController
{
    public function __construct(
        private readonly DealRepository $dealRepository,
        private readonly ParticipantRepository $participantRepository,
    ) {
    }

    public function catchDataFromGoogleForm(
        Request $request,
        Response $response,
        Event $authorizedEvent,
    ): Response {
        $jsonFromBody = $this->getParsedJsonFromBody($request);

        if ($jsonFromBody === []) {
            $this->sentryCollector->collect(new \Exception('No data in request body'));

            return $response->withStatus(400);
        }

        $deal = $this->dealRepository->trySaveNewDealFromGoogleForm($jsonFromBody, $authorizedEvent);
        if ($deal === null) {
            $this->sentryCollector->collect(new \Exception(sprintf(
                'Missing data or invalid auth in request. Body: %s',
                serialize($jsonFromBody),
            )));

            return $response->withStatus(422);
        }

        return $response->withStatus(201);
    }

    public function setDealAsDone(
        Request $request,
        Response $response,
        Event $event,
        int $participantId,
        string $dealSlug,
    ): Response {
        $participant = $this->participantRepository->getParticipantById($participantId, $event);
        $this->dealRepository->setDealAsDone($participant, $dealSlug);

        return $this->redirect(
            $request,
            $response,
            'admin-mend-participant',
            [
                'participantId' => (string)$participantId,
            ],
        );
    }
}
