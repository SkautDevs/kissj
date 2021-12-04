<?php

namespace kissj\Export;

use kissj\Event\Event;
use kissj\Participant\Admin\AdminService;
use kissj\Participant\Ist\Ist;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\User\User;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExportService
{
    public function __construct(
        private ParticipantRepository $participantRepository,
        private AdminService $adminService,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @param Event $event
     * @param User  $adminUser
     * @return array<array<string>>
     */
    public function healthDataToCSV(Event $event, User $adminUser): array
    {
        $participants = $this->participantRepository->getAllPaidParticipantsFromEvent($event);
        $participants = $this->adminService->filterContingentAdminParticipants($adminUser, $participants);

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
                $participant->role,
                $participant->user?->status,
                $this->translator->trans($participant->contingent ?? ''),
                $participant->firstName,
                $participant->lastName, // 5
                $participant->user?->email,
                $participant->email,
            ];
        }

        return $rows;
    }

    /**
     * @param Event $event
     * @param User  $adminUser
     * @return array<array<string>>
     */
    public function paidContactDataToCSV(Event $event, User $adminUser): array
    {
        $participants = $this->participantRepository->getAllPaidParticipantsFromEvent($event);
        $participants = $this->adminService->filterContingentAdminParticipants($adminUser, $participants);

        $rows = [];
        $rows[] = [
            'id', // 0
            'role',
            'status',
            'contingent',
            'name',
            'surname', // 5
            'registration email',
            'contact email',
        ];

        foreach ($participants as $participant) {
            $rows[] = [
                (string)$participant->id, // 0
                $participant->role,
                $participant->user?->status,
                $this->translator->trans($participant->contingent ?? ''),
                $participant->firstName,
                $participant->lastName, // 5
                $participant->user?->email,
                $participant->email,
            ];
        }

        return $rows;
    }

    /**
     * @param Event $event
     * @param User  $adminUser
     * @return array<array<string>>
     */
    public function allRegistrationDataToCSV(Event $event, User $adminUser): array
    {
        $participants = $this->participantRepository->getAllNonOpenParticipantsFromEvent($event);
        $participants = $this->adminService->filterContingentAdminParticipants($adminUser, $participants);

        $rows[] = [
            'id', // 0
            'role',
            'status',
            'contingent',
            'firstName',
            'lastName', // 5
            'nickname',
            'permanentResidence',
            'telephoneNumber',
            'gender',
            'country', // 10
            'email_user',
            'email_custom',
            'scoutUnit',
            'languages',
            'birthDate', // 15
            'birthPlace',
            'healthProblems',
            'foodPreferences',
            'idNumber',
            'swimming', // 20
            'tshirt',
            'arrivalDate',
            'departureDate',
            'uploadedOriginalFilename',
            'notes', // 25
            'patrolLeaderId_patrolParticipantId',
            'patrolName',
            'istSkills',
            'istPreferredPosition',
            'driverLicense', // 30
        ];

        foreach ($participants as $participant) {
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
                    (string)$participant->id, // 0
                    $participant->role,
                    $participant->user?->status,
                    $this->translator->trans($participant->contingent ?? ''),
                    $participant->firstName,
                    $participant->lastName, // 5
                    $participant->nickname,
                    $participant->permanentResidence,
                    $participant->telephoneNumber,
                    $participant->gender,
                    $participant->country, // 10
                    $participant->user?->email,
                    $participant->email,
                    $participant->scoutUnit,
                    $participant->languages,
                    $participant->birthDate ? $participant->birthDate->format('d. m. Y') : '', // 15
                    $participant->birthPlace,
                    $participant->healthProblems,
                    $this->translator->trans($participant->foodPreferences ?? ''),
                    $participant->idNumber,
                    $participant->swimming, // 20
                    $this->translator->trans($participant->getTshirtSize() ?? '')
                    . ' - ' . $this->translator->trans($participant->getTshirtShape() ?? ''),
                    $participant->arrivalDate,
                    $participant->departureDate,
                    $participant->uploadedOriginalFilename,
                    $participant->notes, // 25
                ],
                $pPart,
                $istPart
            );
        }

        return $rows;
    }
}
