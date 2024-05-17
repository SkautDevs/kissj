<?php

declare(strict_types=1);

namespace kissj\Entry;

use kissj\AbstractController;
use kissj\Event\Event;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EntryController extends AbstractController
{
    public function __construct(
        private readonly ParticipantRepository $participantRepository,
        private readonly ParticipantService $participantService,
    ) {
    }

    public function list(
        Response $response,
        Event $authorizedEvent,
    ): Response {
        $participants = $this->participantRepository->getParticipantsForEntry($authorizedEvent);

        return $this->getResponseWithJson(
            $response,
            [
                'eventName' => $authorizedEvent->readableName,
                'eventSlug' => $authorizedEvent->slug,
                'roles' => $participants,
            ],
        );
    }

    public function entry(
        Request $request,
        Response $response,
        string $entryCode,
    ): Response {
        // TODO solve code ABCDEFGH - default code for DB - block or generate better data in DB
        $participant = $this->participantRepository->findOneByEntryCode($entryCode);
        if ($participant === null) {
            return $this->getResponseWithJson(
                $response,
                [
                    'status' => EntryStatus::ENTRY_STATUS_INVALID,
                    'reason' => 'participant not found',
                ],
                403,
            );
        }

        $bodyJson = $this->getParsedJsonFromBody($request);
        if (!array_key_exists('eventSecret', $bodyJson)) {
            return $this->getResponseWithJson(
                $response,
                [
                    'status' => EntryStatus::ENTRY_STATUS_INVALID,
                    'reason' => 'in JSON request key eventSecret is missing',
                ],
                422,
            );
        }
        $eventSecret = $bodyJson['eventSecret'];
        $event = $participant->getUserButNotNull()->event;

        if ($event->apiSecret !== $eventSecret) {
            return $this->getResponseWithJson(
                $response,
                [
                    'status' => EntryStatus::ENTRY_STATUS_INVALID,
                    'reason' => 'invalid event secret',
                ],
                403,
            );
        }

        $participantInfo = [
            'eventName' => $event->readableName,
            'fullName' => $participant->getFullName(),
            'email' => $participant->email,
            'ageAtEventStart' => $participant->getAgeAtStartOfEvent(),
        ];

        if ($participant->entryDate !== null) {
            return $this->getResponseWithJson(
                $response,
                $participantInfo + [
                    'status' => EntryStatus::ENTRY_STATUS_USED,
                    'entryDateTime' => $participant->entryDate->format(DATE_ATOM),
                ],
            );
        }

        $this->participantService->setAsEntered($participant);

        return $this->getResponseWithJson(
            $response,
            $participantInfo + [
                'status' => EntryStatus::ENTRY_STATUS_VALID,
            ],
        );
    }

    public function entryFromWebApp(
        Response $response,
        Event $authorizedEvent,
        int $participantId,
    ): Response {
        $participant = $this->participantRepository->findParticipantById($participantId, $authorizedEvent);
        if ($participant === null) {
            return $this->getResponseWithJson(
                $response,
                [
                    'status' => EntryStatus::ENTRY_STATUS_INVALID,
                    'reason' => 'participant not found',
                ],
                403,
            );
        }

        if ($participant->entryDate !== null) {
            return $this->getResponseWithJson(
                $response,
                [
                    'status' => EntryStatus::ENTRY_STATUS_USED,
                    'entryDateTime' => $participant->entryDate->format(DATE_ATOM),
                ],
            );
        }

        $this->participantService->setAsEntered($participant);

        return $this->getResponseWithJson(
            $response,
            [
                'status' => EntryStatus::ENTRY_STATUS_VALID,
            ],
        );
    }

    public function entryFromAdmin(Request $request, Response $response, Event $event, int $participantId): Response
    {
        $participant = $this->participantRepository->getParticipantById($participantId, $event);
        $this->participantService->setAsEntered($participant);
        $this->logger->info('Participant with ID ' . $participantId . ' set as entered from admin');

        return $this->redirect(
            $request,
            $response,
            'admin-mend-participant',
            [
				'participantId' => (string)$participantId,
			],
        );
    }

    public function unentryFromAdmin(Request $request, Response $response, Event $event, int $participantId): Response
    {
        $participant = $this->participantRepository->getParticipantById($participantId, $event);
        $this->participantService->setAsUnentered($participant);
        $this->logger->info('Participant with ID ' . $participantId . ' set as NOT entered from admin');

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
