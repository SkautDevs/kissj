<?php

declare(strict_types=1);

namespace kissj\Entry;

use kissj\AbstractController;
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

    public function entry(string $entryCode, Request $request, Response $response): Response
    {
        $participant = $this->participantRepository->findOneByEntryCode($entryCode);
        if ($participant === null) {
            return $this->getResponseWithJson(
                $response,
                ['status' => 'unvalid', 'reason' => 'participant not found'],
                403,
            );
        }

        $eventSecret = $this->getParameterFromBody($request, 'eventSecret');
        $event = $participant->getUserButNotNull()->event;
        if ($event->apiSecret !== $eventSecret) {
            return $this->getResponseWithJson(
                $response,
                ['status' => 'unvalid', 'rason' => 'unvalid event secret'],
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
                    'status' => 'used',
                    'entryDateTime' => $participant->entryDate->format(DATE_ATOM),
                ],
            );
        }
        
        $this->participantService->setAsEntered($participant);
        
        return $this->getResponseWithJson(
            $response,
            $participantInfo + [
                'status' => 'valid',
            ]
        );
    }
}
