<?php

declare(strict_types=1);

namespace kissj\Export;

use kissj\Event\Event;
use kissj\Orm\Order;
use kissj\Participant\Ist\Ist;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Participant\Troop\TroopLeader;
use kissj\Participant\Troop\TroopParticipant;
use kissj\User\User;
use kissj\User\UserStatus;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class ExportService
{
    public function __construct(
        private ParticipantRepository $participantRepository,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function healthDataToCSV(Event $event, User $adminUser): array
    {
        $participants = $this->participantRepository->getAllParticipantsWithStatus(
            [
                ParticipantRole::PatrolLeader,
                ParticipantRole::PatrolParticipant,
                ParticipantRole::TroopLeader,
                ParticipantRole::TroopParticipant,
                ParticipantRole::Ist,
                ParticipantRole::Guest,
            ],
            [UserStatus::Paid],
            $event,
            $adminUser,
            sortedByTroopOrPatrol: true,
        );

        $rows = [];
        $rows[] = [
            'id', // 0
            'role',
            'status',
            'contingent',
            'name',
            'surname', // 5
            'gender',
            'health informations',
            'medicaments',
            'psychical health',
            'swimming', // 10
            'note',
        ];

        foreach ($participants as $participant) {
            $rows[] = [
                (string)$participant->id, // 0
                $participant->role?->value ?? '',
                $participant->user?->status->value ?? '',
                $this->translator->trans($participant->contingent ?? ''),
                $participant->firstName ?? '',
                $participant->lastName ?? '', // 5
                $participant->gender ?? '',
                $participant->healthProblems ?? '',
                $participant->medicaments ?? '',
                $participant->psychicalHealthProblems ?? '',
                $participant->swimming ?? '', // 10
                $participant->notes ?? '',
            ];
        }

        return $rows;
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function paidContactDataToCSV(Event $event, User $adminUser): array
    {
        $participants = $this->participantRepository->getAllParticipantsWithStatus(
            [
                ParticipantRole::PatrolLeader,
                ParticipantRole::PatrolParticipant,
                ParticipantRole::TroopLeader,
                ParticipantRole::TroopParticipant,
                ParticipantRole::Ist,
                ParticipantRole::Guest,
            ],
            [UserStatus::Paid],
            $event,
            $adminUser,
            [new Order(Order::COLUMN_UPDATED_AT)],
        );

        $rows = [];
        $rows[] = [
            'id', // 0
            'role',
            'patrol participants',
            'contingent',
            'nickname',
            'first name', // 5
            'surname',
            'registration email',
            'contact email',
        ];

        foreach ($participants as $participant) {
            if (!$participant instanceof PatrolParticipant) {
                $rows[] = [
                    (string)$participant->id, // 0
                    $participant->role?->value ?? '',
                    match (true) {
                        $participant instanceof PatrolLeader => (string)$participant->getPatrolParticipantsCount(),
                        $participant instanceof TroopLeader => (string)$participant->getTroopParticipantsCount(),
                        default => '',
                    },
                    $this->translator->trans($participant->contingent ?? ''),
                    $participant->nickname ?? '',
                    $participant->firstName ?? '', // 5
                    $participant->lastName ?? '',
                    $participant->user?->email ?? '',
                    $participant->email ?? '',
                ];
            }
        }

        return $rows;
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function allRegistrationDataToCSV(Event $event, User $adminUser): array
    {
        $participants = $this->participantRepository->getAllParticipantsWithStatus(
            [
                ParticipantRole::PatrolLeader,
                ParticipantRole::PatrolParticipant,
                ParticipantRole::TroopLeader,
                ParticipantRole::TroopParticipant,
                ParticipantRole::Ist,
                ParticipantRole::Guest,
            ],
            [UserStatus::Closed, UserStatus::Approved, UserStatus::Paid],
            $event,
            $adminUser,
            sortedByTroopOrPatrol: true,
        );

        $rows = [[
            'id', // 0
            'eventName',
            'role',
            'status',
            'contingent',
            'firstName', // 5
            'lastName',
            'nickname',
            'permanentResidence',
            'telephoneNumber',
            'gender', // 10
            'country',
            'email_user',
            'email_custom',
            'scoutUnit',
            'languages', // 15
            'birthDate',
            'birthPlace',
            'healthProblems',
            'medicaments',
            'psychicalHealthProblems', // 20
            'foodPreferences',
            'foodPreferencesRaw',
            'idNumber',
            'scarf',
            'swimming', // 25
            'tshirt',
            'arrivalDate',
            'departureDate',
            'uploadedOriginalFilename',
            'printedHandbook', // 30
            'notes',
            'updatedAt',
            'registrationCloseDate',
            'registrationApproveDate',
            'registrationPayDate', // 35
            'entryDate',
            'leaveDate',
            'patrolOrTroopLeaderId',
            'patrolName',
            'patrolParticipantCount',
            'istSkills', // 40
            'istPreferredPosition',
            'driverLicense',
        ]];

        foreach ($participants as $participant) {
            $ptPart = match (true) {
                $participant instanceof PatrolLeader => [
                    (string)$participant->id,
                    $participant->patrolName ?? '',
                    (string)$participant->getPatrolParticipantsCount(),
                ],
                $participant instanceof TroopLeader => [
                    (string)$participant->id,
                    $participant->patrolName ?? '',
                    (string)$participant->getTroopParticipantsCount(),
                ],
                $participant instanceof PatrolParticipant => [
                    (string)$participant->patrolLeader->id,
                    $participant->patrolLeader->patrolName ?? '',
                    '',
                ],
                $participant instanceof TroopParticipant => [
                    (string)$participant->troopLeader?->id,
                    $participant->troopLeader->patrolName ?? '',
                    '',
                ],
                default => [
                    '',
                    '',
                    '',
                ],
            };

            if ($participant instanceof Ist) {
                $istPart = [
                    $participant->skills ?? '',
                    implode(' | ', $participant->preferredPosition),
                    $participant->driversLicense ?? '',
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
                    (string)$participant->id, // 0
                    $participant->user?->event->readableName ?? '',
                    $participant->role?->value ?? '',
                    $participant->user?->status->value ?? '',
                    $this->translator->trans($participant->contingent ?? ''),
                    $participant->firstName ?? '', // 5
                    $participant->lastName ?? '',
                    $participant->nickname ?? '',
                    $participant->permanentResidence ?? '',
                    $participant->telephoneNumber ?? '',
                    $participant->gender ?? '', // 10
                    $participant->country ?? '',
                    $participant->getUserButNotNull()->email,
                    $participant->email ?? '',
                    $participant->scoutUnit ?? '',
                    $participant->languages ?? '', // 15
                    $participant->birthDate !== null ? $participant->birthDate->format('d. m. Y') : '',
                    $participant->birthPlace ?? '',
                    $participant->healthProblems ?? '',
                    $participant->medicaments ?? '',
                    $participant->psychicalHealthProblems ?? '', // 20
                    $this->translator->trans($participant->foodPreferences ?? ''),
                    $participant->foodPreferences ?? '',
                    $participant->idNumber ?? '',
                    $participant->scarf ?? '',
                    $this->translator->trans($participant->swimming ?? ''), //25
                    $this->translator->trans($participant->getTshirtSize() ?? '')
                        . ' - ' . $this->translator->trans($participant->getTshirtShape() ?? ''),
                    $participant->arrivalDate !== null ? $participant->arrivalDate->format('d. m. Y') : '',
                    $participant->departureDate !== null ? $participant->departureDate->format('d. m. Y') : '',
                    $participant->uploadedOriginalFilename ?? '', // 30
                    $participant->printedHandbook !== null ? (string)$participant->printedHandbook : '',
                    $participant->notes ?? '',
                    $participant->updatedAt !== null ? $participant->updatedAt->format('d. m. Y H:i:s') : '',
                    $participant->registrationCloseDate !== null ? $participant->registrationCloseDate->format('d. m. Y H:i:s') : '',
                    $participant->registrationApproveDate !== null ? $participant->registrationApproveDate->format('d. m. Y H:i:s') : '', // 35
                    $participant->registrationPayDate !== null ? $participant->registrationPayDate->format('d. m. Y H:i:s') : '',
                    $participant->entryDate !== null ? $participant->entryDate->format('d. m. Y H:i:s') : '',
                    $participant->leaveDate !== null ? $participant->leaveDate->format('d. m. Y H:i:s') : '',
                ],
                $ptPart,
                $istPart
            );
        }

        return $rows;
    }
}
