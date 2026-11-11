<?php

declare(strict_types=1);

namespace kissj\Programme;

use kissj\Application\DateTimeUtils;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\Programme\DTO\ProgrammeApiProgrammeDTO;
use kissj\Telemetry\MetricName;
use kissj\Telemetry\Metrics;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserStatus;

readonly class ProgrammeService
{
    public function __construct(
        private ProgrammeRepository $programmeRepository,
        private ProgrammeRegistrationRepository $programmeRegistrationRepository,
        private ParticipantRepository $participantRepository,
        private UserRepository $userRepository,
        private Metrics $metrics,
    ) {
    }

    public function register(Participant $participant, int $programmeId): ProgrammeRegistrationResult
    {
        $result = $this->programmeRepository->transactional(
            function () use ($participant, $programmeId): ProgrammeRegistrationResult {
                $this->participantRepository->lock($participant);

                $refusal = $this->findSelfServiceRefusal(
                    $this->userRepository->get($participant->getUserButNotNull()->id),
                );
                if ($refusal !== null) {
                    return $refusal;
                }

                $programme = $this->programmeRepository->findWithLock($programmeId);

                if (
                    $programme === null
                    || $programme->isDeleted()
                    || $programme->event->id !== $participant->getUserButNotNull()->event->id
                ) {
                    return ProgrammeRegistrationResult::failure(ProgrammeRegistrationError::NotFound);
                }

                if ($programme->isPreregistered) {
                    return ProgrammeRegistrationResult::failure(ProgrammeRegistrationError::NotPossible);
                }

                if ($programme->isForRole($participant->getRoleOrFail()) === false) {
                    return ProgrammeRegistrationResult::failure(ProgrammeRegistrationError::WrongRole);
                }

                if ($this->programmeRegistrationRepository->findActive($programme, $participant) !== null) {
                    return ProgrammeRegistrationResult::failure(ProgrammeRegistrationError::AlreadyRegistered);
                }

                foreach ($this->programmeRegistrationRepository->findActiveForParticipant($participant) as $existing) {
                    if ($programme->isExclusiveWith($existing->programme)) {
                        return ProgrammeRegistrationResult::failure(ProgrammeRegistrationError::Conflict);
                    }
                }

                $taken = $this->programmeRegistrationRepository->countActiveForProgramme($programme);
                if ($taken >= $programme->capacity) {
                    return ProgrammeRegistrationResult::failure(ProgrammeRegistrationError::Full);
                }

                $this->createRegistration($programme, $participant);

                return ProgrammeRegistrationResult::success('flash.success.programmeRegistered');
            },
        );

        $this->countOutcome($participant, $result, MetricName::ProgrammesRegistered);

        return $result;
    }

    public function unregister(Participant $participant, int $programmeId): ProgrammeRegistrationResult
    {
        $refusal = $this->findSelfServiceRefusal($participant->getUserButNotNull());
        if ($refusal !== null) {
            $this->countOutcome($participant, $refusal, MetricName::ProgrammesUnregistered);

            return $refusal;
        }

        $result = $this->programmeRepository->transactional(
            function () use ($participant, $programmeId): ProgrammeRegistrationResult {
                $programme = $this->programmeRepository->findWithLock($programmeId);

                if (
                    $programme === null
                    || $programme->isDeleted()
                    || $programme->event->id !== $participant->getUserButNotNull()->event->id
                ) {
                    return ProgrammeRegistrationResult::failure(ProgrammeRegistrationError::NotFound);
                }

                if ($programme->isPreregistered) {
                    return ProgrammeRegistrationResult::failure(ProgrammeRegistrationError::NotPossible);
                }

                $registration = $this->programmeRegistrationRepository->findActive($programme, $participant);
                if ($registration === null) {
                    return ProgrammeRegistrationResult::failure(ProgrammeRegistrationError::NotRegistered);
                }

                $this->programmeRegistrationRepository->softDelete($registration);

                return ProgrammeRegistrationResult::success('flash.success.programmeUnregistered');
            },
        );

        $this->countOutcome($participant, $result, MetricName::ProgrammesUnregistered);

        return $result;
    }

    public function adminAssign(Programme $programme, Participant $participant): ProgrammeRegistrationResult
    {
        if ($programme->isPreregistered) {
            return ProgrammeRegistrationResult::failure(ProgrammeRegistrationError::PreregisteredNoAssign);
        }

        return $this->programmeRepository->transactional(
            function () use ($programme, $participant): ProgrammeRegistrationResult {
                $this->participantRepository->lock($participant);

                $user = $this->userRepository->get($participant->getUserButNotNull()->id);
                if ($user->status === UserStatus::Cancelled) {
                    return ProgrammeRegistrationResult::failure(ProgrammeRegistrationError::CancelledNoAssign);
                }

                $lockedProgramme = $this->programmeRepository->findWithLock($programme->id);
                if ($lockedProgramme === null || $lockedProgramme->isDeleted()) {
                    return ProgrammeRegistrationResult::failure(ProgrammeRegistrationError::NotFound);
                }

                if ($this->programmeRegistrationRepository->findActive($programme, $participant) !== null) {
                    return ProgrammeRegistrationResult::failure(ProgrammeRegistrationError::AlreadyAssigned);
                }

                $this->createRegistration($programme, $participant);

                return ProgrammeRegistrationResult::success('flash.success.programmeParticipantAssigned');
            },
        );
    }

    public function adminRemove(Programme $programme, Participant $participant): ProgrammeRegistrationResult
    {
        return $this->programmeRepository->transactional(
            function () use ($programme, $participant): ProgrammeRegistrationResult {
                $this->programmeRepository->findWithLock($programme->id);
                $registration = $this->programmeRegistrationRepository->findActive($programme, $participant);
                if ($registration === null) {
                    return ProgrammeRegistrationResult::failure(ProgrammeRegistrationError::NotAssigned);
                }

                $this->programmeRegistrationRepository->softDelete($registration);

                return ProgrammeRegistrationResult::success('flash.success.programmeParticipantRemoved');
            },
        );
    }

    public function deleteProgramme(Programme $programme): void
    {
        $this->programmeRepository->transactional(
            function () use ($programme): void {
                $this->programmeRegistrationRepository->softDeleteAllActiveForProgramme($programme);
                $programme->deletedAt = DateTimeUtils::getDateTime();
                $this->programmeRepository->persist($programme);
            },
        );
    }

    public function freeAllProgrammes(Participant $participant): void
    {
        $this->programmeRegistrationRepository->softDeleteAllActiveForParticipant($participant);
    }

    /**
     * @return list<ProgrammeRegistration>
     */
    public function getDashboardRegistrations(Participant $participant): array
    {
        if ($participant->getUserButNotNull()->status !== UserStatus::Paid) {
            return [];
        }

        return $this->programmeRegistrationRepository->findActiveForParticipant($participant);
    }

    /**
     * @return list<Programme>
     */
    public function getDashboardPreregisteredProgrammes(Participant $participant): array
    {
        $user = $participant->getUserButNotNull();
        if ($user->status !== UserStatus::Paid) {
            return [];
        }

        return $this->programmeRepository->findActivePreregisteredForRole($user->event, $participant->getRoleOrFail());
    }

    public function presentProgramme(Programme $programme): ProgrammeApiProgrammeDTO
    {
        return new ProgrammeApiProgrammeDTO(
            $programme->id,
            $programme->name,
            $programme->section->id,
            $programme->description,
            $programme->place,
            DateTimeUtils::formatAtom($programme->start),
            DateTimeUtils::formatAtom($programme->end),
            $programme->isPreregistered,
            array_map(
                static fn (ParticipantRole $role): string => $role->value,
                $programme->getTargetRoles(),
            ),
        );
    }

    private function findSelfServiceRefusal(User $user): ?ProgrammeRegistrationResult
    {
        if ($user->status !== UserStatus::Paid) {
            return ProgrammeRegistrationResult::failure(ProgrammeRegistrationError::NotPaid);
        }

        if ($user->event->isProgrammeRegistrationOpen() === false) {
            return ProgrammeRegistrationResult::failure(ProgrammeRegistrationError::Closed);
        }

        return null;
    }

    private function countOutcome(
        Participant $participant,
        ProgrammeRegistrationResult $result,
        MetricName $successMetric,
    ): void {
        $attributes = ['event' => $participant->getUserButNotNull()->event->slug];

        if ($result->error === null) {
            $this->metrics->count($successMetric, 1, $attributes);

            return;
        }

        $this->metrics->count(
            MetricName::ProgrammesRegistrationRefused,
            1,
            [...$attributes, 'error' => $result->error->value, 'action' => $successMetric->value],
        );
    }

    private function createRegistration(Programme $programme, Participant $participant): void
    {
        $registration = new ProgrammeRegistration();
        $registration->programme = $programme;
        $registration->participant = $participant;
        $this->programmeRegistrationRepository->persist($registration);
    }
}
