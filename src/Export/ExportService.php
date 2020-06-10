<?php

namespace kissj\Export;

use kissj\Event\Event;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use kissj\User\User;
use League\Csv\Reader;
use League\Csv\Writer;
use Psr\Http\Message\MessageInterface as Response;


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

        $response = $response->withAddedHeader('Content-Type', 'text/csv');
        $response = $response->withAddedHeader('Content-Disposition', 'attachment; filename="'.$fileName.'.csv";');
        $response = $response->withAddedHeader('Expires', '0');
        $response = $response->withAddedHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response = $response->withAddedHeader('Pragma', 'no-cache');

        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        $csv->setDelimiter(',');
        $csv->setOutputBOM(Reader::BOM_UTF8);
        $csv->insertAll($csvRows);

        $body = $response->getBody();
        $body->write($csv->getContent());

        return $response->withBody($body);
    }

    public function healthDataToCSV(Event $event): array {
        // TODO add event-aware
        $paidIsts = $this->participantService
            ->getAllParticipantsWithStatus(User::ROLE_IST, User::STATUS_PAID);
        /*$paidPatrolLeaders = $this->participantService
            ->getAllParticipantsWithStatus(User::ROLE_PATROL_LEADER, User::STATUS_PAID);
        $paidFreeParticipants = $this->participantService
            ->getAllParticipantsWithStatus(User::ROLE_FREE_PARTICIPANT, User::STATUS_PAID);
        $approvedGuests = $this->participantService
            ->getAllParticipantsWithStatus(User::ROLE_GUEST, User::STATUS_PAID);*/

        $rows = [];
        $rows[] = [
            'id',
            //'role',
            'first name',
            'last name',
            'gender',
            'birth day',
            'scout unit',
            'country',
            'health problems',
            'notice/responsible person when 18-',
        ];

        $namedGroups = [
            'IST' => $paidIsts,
            /*'Patrol Leaders' => $paidPatrolLeaders,
            'Free Participants' => $paidFreeParticipants,
            'Guests' => $approvedGuests,*/
        ];
        foreach ($namedGroups as $groupName => $participantGroup) {
            /*$rows[] = [
                '',
                $groupName,
                '',
                '',
                '',
                '',
                '',
                '',
            ];*/

            foreach ($participantGroup as $participant) {
                $rows[] = [
                    $participant->id,
                    //$participant->user->role,
                    $participant->firstName,
                    $participant->lastName,
                    $participant->gender,
                    $participant->birthDate->format('d.m.Y'),
                    $participant->scoutUnit,
                    $participant->country,
                    $participant->healthProblems,
                    $participant->notes,
                ];
            }
        }

        return $rows;
    }

    public function paidContactDataToCSV(Event $event): array {
        // TODO add event-aware
        $paidIsts = $this->participantService
            ->getAllParticipantsWithStatus(User::ROLE_IST, User::STATUS_PAID);
        /*$paidPatrolLeaders = $this->participantService
            ->getAllParticipantsWithStatus(User::ROLE_PATROL_LEADER, User::STATUS_PAID);
        $paidFreeParticipants = $this->participantService
            ->getAllParticipantsWithStatus(User::ROLE_FREE_PARTICIPANT, User::STATUS_PAID);
        $approvedGuests = $this->participantService
            ->getAllParticipantsWithStatus(User::ROLE_GUEST, User::STATUS_PAID);*/

        $rows = [];
        $rows[] = [
            'id',
            //'role',
            'name',
            'surname',
            'registration email',
            //'contact email',
        ];

        $namedGroups = [
            'IST' => $paidIsts,
            /*'Patrol Leaders' => $paidPatrolLeaders,
            'Free Participants' => $paidFreeParticipants,
            'Guests' => $approvedGuests,*/
        ];
        foreach ($namedGroups as $groupName => $participantGroup) {
            /*$rows[] = [
                '',
                $groupName,
                '',
                //'',
            ];*/

            foreach ($participantGroup as $participant) {
                $rows[] = [
                    $participant->id,
                    //$participant->user->role,
                    $participant->firstName,
                    $participant->lastName,
                    $participant->user->email,
                    '', //$participant->email,
                ];
            }
        }

        return $rows;
    }

    public function allRegistrationDataToCSV(Event $event): array {
        // TODO add event-aware
        $participants = $this->participantRepository->findAll();

        $rows[] = [
            'id',
            'role',
            'status',
            'first name',
            'last name',
            'nickname',
            'registration email',
            'birth date',
            'gender',
            'permanent residence',
            'country',
            'health problems',
            'scout unit',
            'scarf',
            'notes',
        ];

        /** @var Participant $participant */
        foreach ($participants as $participant) {
            if ($participant->user->status === User::STATUS_OPEN) {
                continue;
            }
            /*if ($participant instanceof PatrolLeader) {
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
            }*/

            $rows[] = array_merge(
                [
                    (string)$participant->id,
                    $participant->role,
                    $participant->user->status,
                    $participant->firstName,
                    $participant->lastName,
                    $participant->nickname,
                    $participant->user->email,
                    $participant->birthDate ? $participant->birthDate->format('d. m. Y') : '',
                    $participant->gender,
                    $participant->permanentResidence,
                    $participant->country,
                    $participant->healthProblems,
                    $participant->scoutUnit,
                    $participant->scarf,
                    $participant->notes,
                ]
            /*$pPart,
            $freeParticipantPart,
            $istPart*/
            );
        }

        return $rows;
    }
}
