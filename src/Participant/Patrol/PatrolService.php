<?php

declare(strict_types=1);

namespace kissj\Participant\Patrol;

use kissj\Application\DateTimeUtils;
use kissj\Mailer\Mailer;
use kissj\Participant\ParticipantRole;
use kissj\Participant\ParticipantService;
use kissj\Participant\RegistrationCloseResult;
use kissj\Skautis\SkautisMemberData;
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

    public function addPatrolParticipant(PatrolLeader $patrolLeader): PatrolParticipant
    {
        $patrolParticipant = new PatrolParticipant();
        $patrolParticipant->patrolLeader = $patrolLeader;

        $this->patrolParticipantRepository->persist($patrolParticipant);

        return $patrolParticipant;
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
        $patrolParticipant->gender = $memberData->getGender()->value;
        $patrolParticipant->permanentResidence = $memberData->getPermanentResidence();
        $patrolParticipant->country = $memberData->getCountry()->value;

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

    public function isCloseRegistrationValid(PatrolLeader $patrolLeader): RegistrationCloseResult
    {
        $result = $this->participantService->isCloseRegistrationValid($patrolLeader);
        $event = $patrolLeader->getUserButNotNull()->event;
        $participants = $patrolLeader->patrolParticipants;

        $participantsCount = count($participants);
        if ($participantsCount < $event->getMinimalPpCount($patrolLeader)) {
            $result = $result->withWarning('flash.warning.plTooFewParticipants', [
                '%minimalPatrolParticipantsCount%' => (string)$event->getMinimalPpCount($patrolLeader),
            ]);
        }
        if ($participantsCount > $event->getMaximalPpCount($patrolLeader)) {
            $result = $result->withWarning('flash.warning.plTooManyParticipants', [
                '%maximalPatrolParticipantsCount%' => (string)$event->getMaximalPpCount($patrolLeader),
            ]);
        }

        $contentArbiterPatrolParticipant = $event->getEventType()->getContentArbiterPatrolParticipant();
        foreach ($participants as $participant) {
            if (!$this->participantService->isParticipantDataValidForClose(
                $participant,
                $contentArbiterPatrolParticipant,
            )) {
                $result = $result->withWarning('flash.warning.plWrongDataParticipant', [
                    '%participantFullName%' => $participant->getFullName(),
                ]);
            }
        }

        return $result;
    }

    public function closeRegistration(PatrolLeader $patrolLeader): PatrolLeader
    {
        if ($this->isCloseRegistrationValid($patrolLeader)->isValid) {
            $user = $patrolLeader->getUserButNotNull();
            $patrolLeader->registrationCloseDate = DateTimeUtils::getDateTime();
            $this->patrolLeaderRepository->persist($patrolLeader);
            $this->userService->setUserClosed($user);
            $this->mailer->sendRegistrationClosed($user);
        }

        return $patrolLeader;
    }

    /**
     * @param list<SkautisMemberData> $members
     * @param list<array{firstName: string|null, lastName: string|null, birthDate: \DateTimeInterface|null}> $existingParticipantsData
     * @return array<int, string|null>
     */
    public static function matchMembersAgainstExisting(
        array $members,
        string $leaderFirstName,
        string $leaderLastName,
        \DateTimeInterface $leaderBirthDate,
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
