<?php

namespace kissj\Export;

use kissj\Orm\Relation;
use kissj\Participant\Ist\Ist;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\User\User;
use League\Csv\Reader;
use League\Csv\Writer;
use Slim\Http\Response;


class ExportService {

    private $participantRepository;

    public function __construct(ParticipantRepository $participantRepository) {
        $this->participantRepository = $participantRepository;
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

    // TODO fix
    public function logisticDataPatrolsToCSV(string $event): array {
        /** @var Role[] $roles */
        $roles = $this->getExportRoles($event);
        $patrolLeaderUserIds = [];
        foreach ($roles as $role) {
            if ($role->name === 'patrol-leader') {
                $patrolLeaderUserIds[] = $role->user->id;
            }
        }
        /** @var PatrolLeader[] $patrolLeaders */
        $patrolLeaders = $this->patrolLeaderRepository->findBy([
            'userId' => new Relation($patrolLeaderUserIds, 'IN')
        ]);

        $patrolLeaderIds = array_map(function (PatrolLeader $p) {
            return $p->id;
        }, $patrolLeaders);

        /** @var PatrolParticipant[] $partolParticipants */
        $partolParticipants = $this->patrolParticipantRepository->findBy([
            'patrolleaderId' => new Relation($patrolLeaderIds, 'IN')
        ]);

        $rows = [];
        foreach ($patrolLeaders as $leader) {
            $rows[] = [
                $leader->id,
                $leader->permanentResidence,
                $leader->country,
                $leader->firstName.' '.$leader->lastName,
                $leader->email,
            ];
        }
        foreach ($partolParticipants as $participant) {
            $rows[] = [
                $participant->patrolLeader->id,
                $participant->permanentResidence,
                $participant->country,
            ];
        }

        return $rows;
    }

    // TODO fix
    public function medicalDataToCSV(string $event): array {
        /** @var Role[] $roles */
        $roles = $this->getExportRoles($event);
        $patrolLeaderUserIds = [];
        $istUserIds = [];
        foreach ($roles as $role) {
            if ($role->name === 'patrol-leader') {
                $patrolLeaderUserIds[] = $role->user->id;
            } elseif ($role->name === 'ist') {
                $istUserIds[] = $role->user->id;
            };
        }
        /** @var PatrolLeader[] $patrolLeaders */
        $patrolLeaders = $this->patrolLeaderRepository->findBy([
            'userId' => new Relation($patrolLeaderUserIds, 'IN')
        ]);

        $patrolLeaderIds = array_map(function (PatrolLeader $p) {
            return $p->id;
        }, $patrolLeaders);

        /** @var Ist[] $ists */
        $ists = $this->istRepository->findBy([
            'userId' => new Relation($istUserIds, 'IN')
        ]);
        /** @var PatrolParticipant[] $partolParticipants */
        $partolParticipants = $this->patrolParticipantRepository->findBy([
            'patrolleaderId' => new Relation($patrolLeaderIds, 'IN')
        ]);

        $rows = [];
        foreach ($patrolLeaders as $leader) {
            $rows[] = [
                $leader->firstName,
                $leader->lastName,
                $leader->birthDate == null ? '' : $leader->birthDate->format('Y-m-d'),
                $leader->allergies,
            ];
        }
        foreach ($ists as $ist) {
            $rows[] = [
                $ist->firstName,
                $ist->lastName,
                $ist->birthDate == null ? '' : $ist->birthDate->format('Y-m-d'),
                $ist->allergies,
            ];
        }
        foreach ($partolParticipants as $participant) {
            $rows[] = [
                $participant->firstName,
                $participant->lastName,
                $participant->birthDate == null ? '' : $participant->birthDate->format('Y-m-d'),
                $participant->allergies,
            ];
        }
        return $rows;
    }

    // TODO fix
    public function paidContactDataToCSV(string $event): array {
        // TODO now IST only - add PL
        /** @var Role[] $roles */
        $roles = $this->roleRepository->findByMultiple([
            ['event' => $event,],
            ['status' => 'paid',],
        ]);
        $istUserIds = [];
        foreach ($roles as $role) {
            $istUserIds[] = $role->user->id;
        }
        /** @var Ist[] $ists */
        $ists = $this->istRepository->findBy([
            'userId' => new Relation($istUserIds, 'IN')
        ]);

        $rows = [];

        $rows[] = [
            'ID',
            'Křestní jméno',
            'Příjmení',
            'Přezdívka',
            'Email',
        ];

        foreach ($ists as $ist) {
            $rows[] = [
                $ist->id,
                $ist->firstName,
                $ist->lastName,
                $ist->nickname,
                $ist->email,
            ];
        }
        return $rows;
    }

    public function allRegistrationDataToCSV(string $eventName): array {
        /** @var \kissj\Participant\Participant[] $participants */
        $participants = $this->participantRepository->findAll();

        // nulls headers
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
            'birth place',
            'health problems',
            'food preferences',
            'passprort/ID number',
            'swimming',
            'tshirt',
            'arrival date',
            'departue date',
            'notes',
            'patrol id',
            'patrol name',
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

            if ($participant instanceof Ist) {
                $istPart = [
                    $participant->skills,
                    $participant->preferredPosition,
                    $participant->driversLicense,
                ];
            } else {
                $istPart = [
                    '',
                    '',
                    '',
                ];
            }
            $rows[] = [
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
                    $participant->birthPlace,
                    $participant->healthProblems,
                    $participant->foodPreferences,
                    $participant->idNumber,
                    $participant->swimming,
                    $participant->tshirt,
                    $participant->arrivalDate ? $participant->arrivalDate->format('Ymd-H:i:m') : '',
                    $participant->departueDate ? $participant->departueDate->format('Ymd-H:i:m') : '',
                    $participant->notes,
                ] + $pPart + $istPart;
        }

        return $rows;
    }
}
