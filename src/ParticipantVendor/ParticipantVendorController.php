<?php

declare(strict_types=1);

namespace kissj\ParticipantVendor;

use kissj\AbstractController;
use kissj\Event\Event;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\Troop\TroopParticipant;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ParticipantVendorController extends AbstractController
{
    public function __construct(
        private readonly ParticipantRepository $participantRepository,
    ) {
    }

    public function RetrieveParticipantByTieCode(
        Request $request,
        Response $response,
        Event $authorizedEvent,
        string $TieCode,

    ): Response {
        $participant = $this->participantRepository->findOneByTieCodeAndEvent($TieCode, $authorizedEvent);

        if ($participant == null) {
            return $response->withStatus(404);
        }
        $allowHealthData = (bool)$request->getHeader('Allow-Health')[0];

        $vendoredParticipant = new VendoredParticipantType(
            $participant->role -> value ?? "norole",
            $participant->firstName,
            $participant->lastName,
            $participant->birthDate == null ? null : $participant->birthDate->format('Y-m-d'),
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

        $body = json_encode($participant);

        if ($body === false) { return $response->withStatus(500); }

        return $this->getResponseWithJson(
            $response,
            [
                'participant' => $body,
            ],
        );
    }

}
