<?php

namespace kissj\Export;

use kissj\Participant\FreeParticipant\FreeParticipant;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\User\User;
use League\Csv\Reader;
use League\Csv\Writer;
use Slim\Http\Response;


class ExportService {
    private $participantRepository;
    private $participantService;

    public function __construct(
        ParticipantRepository $participantRepository,
        ParticipantService $participantService
    ) {
        $this->participantRepository = $participantRepository;
        $this->participantService = $participantService;
    }

    public function outputCSVresponse(
        Response $response,
        array $csvRows,
        string $fileName,
        bool $amendTimestamp = true
    ) {
        if ($amendTimestamp) {
            $fileName .= '_'.date(DATE_ATOM);
        }

        $response = $response->withHeader('Content-Type', 'text/csv');
        $response = $response->withHeader('Content-Disposition', 'attachment; filename="'.$fileName.'.csv";');
        $response = $response->withHeader('Expires', '0');
        $response = $response->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response = $response->withHeader('Pragma', 'no-cache');

        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        $csv->setDelimiter(',');
        $csv->setOutputBOM(Reader::BOM_UTF8);
        $csv->insertAll($csvRows);

        $body = $response->getBody();
        $body->write($csv->getContent());

        return $response->withBody($body);
    }

    public function paidContactDataToCSV(string $eventName): array {
        $paidIsts = $this->participantService->getAllParticipantsWithStatus(User::ROLE_IST, User::STATUS_PAID);
        $paidPatrolLeaders
            = $this->participantService->getAllParticipantsWithStatus(User::ROLE_PATROL_LEADER, User::STATUS_PAID);
        $paidFreeParticipants = $this->participantService->getAllParticipantsWithStatus(User::ROLE_FREE_PARTICIPANT, User::STATUS_PAID);
        $approvedGuests = $this->participantService->getAllParticipantsWithStatus(User::ROLE_GUEST, User::STATUS_PAID);

        $rows[] = [
            'id',
            'role',
            'registration email',
            'contact email',
        ];

        $namedGroups = [
            'IST' => $paidIsts,
            'Patrol Leaders' => $paidPatrolLeaders,
            'Free Participants' => $paidFreeParticipants,
            'Guests' => $approvedGuests
        ];
        foreach ($namedGroups as $groupName => $participantGroup) {
            $rows[] = [
                '',
                $groupName,
                '',
                '',
            ];

            foreach ($participantGroup as $participant) {
                $rows[] = [
                    $participant->id,
                    $participant->user->role,
                    $participant->user->email,
                    $participant->email,
                ];
            }
        }

        return $rows;
    }

    public function allRegistrationDataToCSV(string $eventName): array {
        /** @var Participant[] $participants */
        $participants = $this->participantRepository->findAll();

        $rows[] = [
            'id',
            'role',
            'first name',
            'last name',
            'nickname',
            'permanent residence',
            'telephone number',
            'gender',
            'country',
            'registration email',
            'contact email',
            'scout unit',
            'languages',
            'birth date',
            'health problems',
            'food preferences',
            'swimming',
            'tshirt',
            'arrival date',
            'departue date',
            'notes/current leader',
            'patrol id',
            'patrol name',
            'legal representative',
            'skills',
            'preferred positions',
            'drivers license',
        ];

        foreach ($participants as $participant) {
            if ($participant->user->status === User::STATUS_OPEN) {
                continue;
            }
            if ($participant instanceof PatrolLeader) {
                $pPart = [
                    (string)$participant->id,
                    $participant->patrolName,
                ];
            } elseif ($participant instanceof PatrolParticipant) {
                $pPart = [
                    (string)$participant->patrolLeader->id,
                    $participant->patrolLeader->patrolName,
                ];
            } else {
                $pPart = [
                    '',
                    '',
                ];
            }

            if ($participant instanceof FreeParticipant) {
                $freeParticipantPart = [
                    $participant->legalRepresentative,
                ];
            } else {
                $freeParticipantPart = [
                    '',
                ];
            }

            if ($participant instanceof Ist) {
                $istPart = [
                    $participant->skills,
                    implode(' | ', $participant->preferredPosition),
                    $participant->driversLicense,
                ];
            } else {
                $istPart = [
                    '',
                    '',
                    '',
                ];
            }

            $rows[] = array_merge(
                [
                    (string)$participant->id,
                    $participant->role,
                    $participant->firstName,
                    $participant->lastName,
                    $participant->nickname,
                    $participant->permanentResidence,
                    $participant->telephoneNumber,
                    $participant->gender,
                    $participant->country,
                    $participant->user->email,
                    $participant->email,
                    $participant->scoutUnit,
                    $participant->languages,
                    $participant->birthDate ? $participant->birthDate->format('d. m. Y') : '',
                    $participant->healthProblems,
                    $participant->foodPreferences,
                    $participant->swimming,
                    $participant->tshirt,
                    $participant->arrivalDate ? $participant->arrivalDate->format('Ymd-H:i:m') : '',
                    $participant->departueDate ? $participant->departueDate->format('Ymd-H:i:m') : '',
                    $participant->notes,
                ],
                $pPart,
                $freeParticipantPart,
                $istPart
            );
        }

        return $rows;
    }
}
