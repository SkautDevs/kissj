<?php

declare(strict_types=1);

namespace kissj\Export;

use kissj\Event\Event;
use kissj\Orm\Order;
use kissj\Participant\Ist\Ist;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Participant\Troop\TroopLeader;
use kissj\User\User;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExportService
{
    public function __construct(
        private ParticipantRepository $participantRepository,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @param Event $event
     * @param User $adminUser
     * @return array<int, array<int, string>>
     */
    public function healthDataToCSV(Event $event, User $adminUser): array
    {
        $participants = $this->participantRepository->getAllParticipantsWithStatus(
            [
                User::ROLE_PATROL_LEADER,
                User::ROLE_PATROL_PARTICIPANT,
                User::ROLE_TROOP_LEADER,
                User::ROLE_TROOP_PARTICIPANT,
                User::ROLE_IST,
                User::ROLE_GUEST,
            ],
            [User::STATUS_PAID],
            $event,
            $adminUser,
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
            'swimming',
            'note',
        ];

        foreach ($participants as $participant) {
            $rows[] = [
                (string)$participant->id, // 0
                $participant->role ?? '',
                $participant->user?->status ?? '',
                $this->translator->trans($participant->contingent ?? ''),
                $participant->firstName ?? '',
                $participant->lastName ?? '', // 5
                $participant->user?->email ?? '',
                $participant->email ?? '',
            ];
        }

        return $rows;
    }

    /**
     * @param Event $event
     * @param User $adminUser
     * @return array<int, array<int, string>>
     */
    public function paidContactDataToCSV(Event $event, User $adminUser): array
    {
        $participants = $this->participantRepository->getAllParticipantsWithStatus(
            [
                User::ROLE_PATROL_LEADER,
                User::ROLE_PATROL_PARTICIPANT,
                User::ROLE_TROOP_LEADER,
                User::ROLE_TROOP_PARTICIPANT,
                User::ROLE_IST,
                User::ROLE_GUEST,
            ],
            [User::STATUS_PAID],
            $event,
            $adminUser,
            new Order(Order::FILED_UPDATED_AT),
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
                    $participant->role ?? '',
                    match (true) {
                        $participant instanceof PatrolLeader => (string)count($participant->patrolParticipants),
                        $participant instanceof TroopLeader => (string)count($participant->troopParticipants),
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
     * @param Event $event
     * @param User $adminUser
     * @return array<int, array<int, string>>
     */
    public function allRegistrationDataToCSV(Event $event, User $adminUser): array
    {
        $participants = $this->participantRepository->getAllParticipantsWithStatus(
            [
                User::ROLE_PATROL_LEADER,
                User::ROLE_PATROL_PARTICIPANT,
                User::ROLE_TROOP_LEADER,
                User::ROLE_TROOP_PARTICIPANT,
                User::ROLE_IST,
                User::ROLE_GUEST,
            ],
            [User::STATUS_CLOSED, User::STATUS_APPROVED, User::STATUS_PAID],
            $event,
            $adminUser,
        );

        $rows[] = [
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
            'foodPreferences',
            'idNumber', // 20
            'swimming',
            'tshirt',
            'arrivalDate',
            'departureDate',
            'uploadedOriginalFilename', // 25
            'notes',
            'patrolLeaderId_patrolParticipantId',
            'patrolName',
            'istSkills',
            'istPreferredPosition', // 30
            'driverLicense',
        ];

        foreach ($participants as $participant) {
            if ($participant instanceof PatrolLeader) {
                $pPart = [
                    (string)$participant->id,
                    $participant->patrolName ?? '',
                ];
            } elseif ($participant instanceof PatrolParticipant) {
                $pPart = [
                    (string)$participant->patrolLeader->id,
                    $participant->patrolLeader->patrolName ?? '',
                ];
            } else {
                $pPart = [
                    '',
                    '',
                ];
            }

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
                    $participant->role ?? '',
                    $participant->user?->status ?? '',
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
                    $participant->birthDate ? $participant->birthDate->format('d. m. Y') : '',
                    $participant->birthPlace ?? '',
                    $participant->healthProblems ?? '',
                    $this->translator->trans($participant->foodPreferences ?? ''),
                    $participant->idNumber ?? '', // 20
                    $this->translator->trans($participant->swimming ?? ''),
                    $this->translator->trans($participant->getTshirtSize() ?? '')
                    . ' - ' . $this->translator->trans($participant->getTshirtShape() ?? ''),
                    $participant->arrivalDate ? $participant->arrivalDate->format('d. m. Y') : '',
                    $participant->departureDate ? $participant->departureDate->format('d. m. Y') : '',
                    $participant->uploadedOriginalFilename ?? '', // 25
                    $participant->notes ?? '',
                ],
                $pPart,
                $istPart
            );
        }

        return $rows;
    }
}
