<?php

declare(strict_types=1);

namespace kissj\Participant\Patrol;

use DateTimeInterface;
use kissj\Application\DateTimeUtils;
use kissj\Mailer\Mailer;
use kissj\Participant\ParticipantRole;
use kissj\Participant\ParticipantService;
use kissj\Skautis\SkautisMemberData;
use kissj\Telemetry\MetricName;
use kissj\Telemetry\Metrics;
use kissj\User\User;
use kissj\User\UserService;

readonly class PatrolService
{
    public function __construct(
        private PatrolLeaderRepository $patrolLeaderRepository,
        private PatrolParticipantRepository $patrolParticipantRepository,
        private UserService $userService,
        private ParticipantService $participantService,
        private Mailer $mailer,
        private Metrics $metrics,
    ) {
    }

    public function getPatrolLeader(User $user): PatrolLeader
    {
        $patrolLeader = $this->patrolLeaderRepository->findOneBy(['user' => $user]);

        if ($patrolLeader === null) {
            // TODO remove, it is here only for testing
            $patrolLeader = new PatrolLeader();
            $patrolLeader->user = $user;
            $patrolLeader->role = ParticipantRole::PatrolLeader;
            $this->patrolLeaderRepository->persist($patrolLeader);
        }

        return $patrolLeader;
    }

    public function addPatrolParticipantFromSkautis(
        PatrolLeader $patrolLeader,
        SkautisMemberData $memberData,
    ): PatrolParticipant {
        $patrolParticipant = new PatrolParticipant();
        $patrolParticipant->patrolLeader = $patrolLeader;
        $patrolParticipant->firstName = $memberData->firstName;
        $patrolParticipant->lastName = $memberData->lastName;
        $patrolParticipant->nickname = $memberData->nickName;
        $patrolParticipant->birthDate = $memberData->birthday;
        $patrolParticipant->gender = $memberData->gender->value;
        $patrolParticipant->permanentResidence = $memberData->getPermanentResidence();
        $patrolParticipant->country = $memberData->country->value;

        $this->patrolParticipantRepository->persist($patrolParticipant);

        return $patrolParticipant;
    }

    // TODO refactor to repository->get()
    public function getPatrolParticipant(int $patrolParticipantId): PatrolParticipant
    {
        return $this->patrolParticipantRepository->getOneBy(['id' => $patrolParticipantId]);
    }

    public function deletePatrolParticipant(PatrolParticipant $patrolParticipant): void
    {
        $this->patrolParticipantRepository->delete($patrolParticipant);
    }

    public function patrolParticipantBelongsPatrolLeader(
        PatrolParticipant $patrolParticipant,
        PatrolLeader $patrolLeader
    ): bool {
        return $patrolParticipant->patrolLeader->id === $patrolLeader->id;
    }

    public function closeRegistration(PatrolLeader $patrolLeader): PatrolLeader
    {
        if ($this->participantService->isCloseRegistrationValid($patrolLeader)->isValid) {
            $user = $patrolLeader->getUserButNotNull();
            $patrolLeader->registrationCloseDate = DateTimeUtils::getDateTime();
            $this->patrolLeaderRepository->persist($patrolLeader);
            $this->userService->setUserClosed($user);
            $this->mailer->sendRegistrationClosed($user, $patrolLeader);
            $this->metrics->count(
                MetricName::RegistrationsLocked,
                1,
                ['role' => $patrolLeader->role->value ?? 'unknown'],
            );
        }

        return $patrolLeader;
    }

    /**
     * @param list<SkautisMemberData> $members
     * @param list<int> $selectedIds
     */
    public function importParticipantsFromSkautis(
        PatrolLeader $patrolLeader,
        array $members,
        array $selectedIds,
    ): int {
        $matches = $this->buildMemberMatches($patrolLeader, $members);

        $importedCount = 0;
        foreach ($members as $member) {
            if (in_array($member->id, $selectedIds, true) && $matches[$member->id] === null) {
                $this->addPatrolParticipantFromSkautis($patrolLeader, $member);
                $importedCount++;
            }
        }

        return $importedCount;
    }

    /**
     * @param list<SkautisMemberData> $members
     * @return array<int, string|null>
     */
    public function buildMemberMatches(PatrolLeader $patrolLeader, array $members): array
    {
        $existingParticipantsData = array_values(array_map(
            fn (PatrolParticipant $pp) => [
                'firstName' => $pp->firstName,
                'lastName' => $pp->lastName,
                'birthDate' => $pp->birthDate,
            ],
            $patrolLeader->patrolParticipants,
        ));

        return self::matchMembersAgainstExisting(
            $members,
            $patrolLeader->firstName ?? '',
            $patrolLeader->lastName ?? '',
            $patrolLeader->birthDate ?? DateTimeUtils::getDateTime('1900-01-01'),
            $existingParticipantsData,
        );
    }

    /**
     * @param list<SkautisMemberData> $members
     * @param list<array{firstName: string|null, lastName: string|null, birthDate: DateTimeInterface|null}> $existingParticipantsData
     * @return array<int, string|null>
     */
    public static function matchMembersAgainstExisting(
        array $members,
        string $leaderFirstName,
        string $leaderLastName,
        DateTimeInterface $leaderBirthDate,
        array $existingParticipantsData,
    ): array {
        $matches = [];
        foreach ($members as $member) {
            if (
                $member->firstName === $leaderFirstName
                && $member->lastName === $leaderLastName
                && $member->birthday->format('Y-m-d') === $leaderBirthDate->format('Y-m-d')
            ) {
                $matches[$member->id] = 'leader';
                continue;
            }

            $found = false;
            foreach ($existingParticipantsData as $existing) {
                if (
                    $member->firstName === ($existing['firstName'] ?? '')
                    && $member->lastName === ($existing['lastName'] ?? '')
                    && $existing['birthDate'] !== null
                    && $member->birthday->format('Y-m-d') === $existing['birthDate']->format('Y-m-d')
                ) {
                    $matches[$member->id] = 'added';
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $matches[$member->id] = null;
            }
        }

        return $matches;
    }
}
