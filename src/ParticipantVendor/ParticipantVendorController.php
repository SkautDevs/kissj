<?php

declare(strict_types=1);

namespace kissj\ParticipantVendor;

use kissj\AbstractController;
use kissj\Event\Event;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\Troop\TroopParticipant;
use Psr\Http\Message\ResponseInterface as Response;

class ParticipantVendorController extends AbstractController
{
    public function __construct(
        private readonly ParticipantRepository $participantRepository,
    ) {
    }

    public function retrieveParticipantByTieCode(
        Response $response,
        Event $authorizedEvent,
        bool $allowHealthData,
        string $tieCode,
    ): Response {
        $participant = $this->participantRepository->findOneByTieCodeAndEvent($tieCode, $authorizedEvent);

        if ($participant === null) {
            return $response->withStatus(404);
        }

        $vendoredParticipant = new VendoredParticipantType(
            $participant->role->value ?? 'norole',
            $participant->firstName,
            $participant->lastName,
            $participant->birthDate?->format('Y-m-d'),
            $participant->nickname,
        );


        if ($participant instanceof TroopParticipant && $participant->troopLeader !== null) {
            $vendoredParticipant->leaderName = $participant->troopLeader->getFullName();
            $vendoredParticipant->leaderContact = $participant->troopLeader->telephoneNumber;
        }
        if ($allowHealthData) {
            $vendoredParticipant->physicalHealth = $participant->healthProblems;
            $vendoredParticipant->psychicalHealth = $participant->psychicalHealthProblems;
            $vendoredParticipant->medicaments = $participant->medicaments;
        }

        $body = json_encode($vendoredParticipant);

        if ($body === false) {
            return $response->withStatus(500);
        }

        return $this->getResponseWithJson(
            $response,
            [
                'participant' => $body,
            ],
        );
    }
}
