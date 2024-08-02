<?php

declare(strict_types=1);

namespace kissj\Entry;

use kissj\AbstractController;
use kissj\Event\Event;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Troop\TroopLeader;
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
            return $this->createErrorEntryResponse($response, 'participant not found');
        }

        $bodyJson = $this->getParsedJsonFromBody($request);
        if (!array_key_exists('eventSecret', $bodyJson)) {
            return $this->createErrorEntryResponse($response, 'in JSON request key eventSecret is missing');
        }
        $eventSecret = $bodyJson['eventSecret'];
        $event = $participant->getUserButNotNull()->event;

        if ($event->apiSecret !== $eventSecret) {
            return $this->createErrorEntryResponse($response, 'invalid event secret');
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

    public function entryParticipantFromWebApp(
        Response $response,
        Event $authorizedEvent,
        int $participantId,
    ): Response {
        $participant = $this->participantRepository->findParticipantById($participantId, $authorizedEvent);
        if ($participant === null) {
            return $this->createErrorEntryResponse($response, 'participant not found');
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

    public function leaveParticipantFromWebApp(
        Response $response,
        Event $authorizedEvent,
        int $participantId,
    ): Response {
        $participant = $this->participantRepository->findParticipantById($participantId, $authorizedEvent);
        if ($participant === null) {
            return $this->createErrorEntryResponse($response, 'participant not found');
        }

        if ($participant->leaveDate !== null) {
            return $this->getResponseWithJson(
                $response,
                [
                    'status' => EntryStatus::ENTRY_STATUS_VALID,
                    'entryDateTime' => null,
                ],
            );
        }

        $this->participantService->setAsLeaved($participant);

        return $this->getResponseWithJson(
            $response,
            [
                'status' => EntryStatus::ENTRY_STATUS_LEAVED,
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
                'participantId' => (string) $participantId,
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
                'participantId' => (string) $participantId,
            ],
        );
    }

    public function entryGroupFromWebApp(
        Response $response,
        Event $authorizedEvent,
        int $participantId,
    ): Response {
        $participant = $this->participantRepository->findParticipantById($participantId, $authorizedEvent);
        if ($participant instanceof TroopLeader) {
            $groupParticipants = $participant->troopParticipants;
        } elseif ($participant instanceof PatrolLeader) {
            $groupParticipants = $participant->patrolParticipants;
        } else {
            return $this->createErrorEntryResponse($response, 'troop not found');
        }

        foreach (array_merge([$participant], $groupParticipants) as $troopParticipant) {
            if ($troopParticipant->entryDate === null) {
                $this->participantService->setAsEntered($troopParticipant);
            }
        }

        return $this->getResponseWithJson(
            $response,
            [
                'status' => EntryStatus::ENTRY_STATUS_VALID,
            ],
        );
    }

    public function leaveTroopFromWebApp(
        Response $response,
        Event $authorizedEvent,
        int $participantId,
    ): Response {
        $participant = $this->participantRepository->findParticipantById($participantId, $authorizedEvent);
        if ($participant instanceof TroopLeader === false) {
            return $this->createErrorEntryResponse($response, 'troop not found');
        }

        foreach (array_merge([$participant], $participant->troopParticipants) as $troopParticipant) {
            if ($participant->leaveDate === null) {
                $this->participantService->setAsLeaved($troopParticipant);
            }
        }

        return $this->getResponseWithJson(
            $response,
            [
                'status' => EntryStatus::ENTRY_STATUS_LEAVED,
            ],
        );
    }

    private function createErrorEntryResponse(
        Response $response,
        string $reason,
    ): Response {
        return $this->getResponseWithJson(
            $response,
            [
                'status' => EntryStatus::ENTRY_STATUS_INVALID,
                'reason' => $reason,
            ],
            403,
        );
    }
}
