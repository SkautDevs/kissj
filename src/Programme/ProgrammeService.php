<?php

declare(strict_types=1);

namespace kissj\Programme;

use kissj\Application\DateTimeUtils;
use kissj\Participant\Participant;
use kissj\User\UserStatus;

readonly class ProgrammeService
{
    public function __construct(
        private ProgrammeRepository $programmeRepository,
        private ProgrammeRegistrationRepository $programmeRegistrationRepository,
    ) {
    }

    public function register(Participant $participant, int $programmeId): ProgrammeRegistrationResult
    {
        return $this->programmeRepository->transactional(
            function () use ($participant, $programmeId): ProgrammeRegistrationResult {
                $programme = $this->programmeRepository->getForUpdate($programmeId);

                if (
                    $programme === null
                    || $programme->isDeleted()
                    || $programme->isPreregistered
                    || $programme->event->id !== $participant->getUserButNotNull()->event->id
                ) {
                    return new ProgrammeRegistrationResult(false, 'flash.error.programmeNotFound');
                }

                if ($programme->isForRole($participant->getRoleOrFail()) === false) {
                    return new ProgrammeRegistrationResult(false, 'flash.error.programmeWrongRole');
                }

                if ($this->programmeRegistrationRepository->findActive($programme, $participant) !== null) {
                    return new ProgrammeRegistrationResult(false, 'flash.error.programmeAlreadyRegistered');
                }

                foreach ($this->programmeRegistrationRepository->findActiveForParticipant($participant) as $existing) {
                    if ($programme->isExclusiveWith($existing->programme)) {
                        return new ProgrammeRegistrationResult(false, 'flash.error.programmeConflict');
                    }
                }

                $taken = $this->programmeRegistrationRepository->countActiveForProgramme($programme);
                if ($taken >= $programme->capacity) {
                    return new ProgrammeRegistrationResult(false, 'flash.error.programmeFull');
                }

                $this->createRegistration($programme, $participant);

                return new ProgrammeRegistrationResult(
                    true,
                    'flash.success.programmeRegistered',
                    $programme->capacity - $taken - 1,
                );
            },
        );
    }

    public function unregister(Participant $participant, int $programmeId): ProgrammeRegistrationResult
    {
        return $this->programmeRepository->transactional(
            function () use ($participant, $programmeId): ProgrammeRegistrationResult {
                $programme = $this->programmeRepository->getForUpdate($programmeId);

                if ($programme === null || $programme->event->id !== $participant->getUserButNotNull()->event->id) {
                    return new ProgrammeRegistrationResult(false, 'flash.error.programmeNotFound');
                }

                $registration = $this->programmeRegistrationRepository->findActive($programme, $participant);
                if ($registration === null) {
                    return new ProgrammeRegistrationResult(false, 'flash.error.programmeNotRegistered');
                }

                $this->programmeRegistrationRepository->softDelete($registration);

                $activeCount = $this->programmeRegistrationRepository->countActiveForProgramme($programme);
                $remaining = $programme->capacity - $activeCount;

                return new ProgrammeRegistrationResult(true, 'flash.success.programmeUnregistered', $remaining);
            },
        );
    }

    public function adminAssign(Programme $programme, Participant $participant): ProgrammeRegistrationResult
    {
        if ($programme->isPreregistered) {
            return new ProgrammeRegistrationResult(false, 'flash.warning.programmePreregisteredNoAssign');
        }

        if ($participant->getUserButNotNull()->status === UserStatus::Cancelled) {
            return new ProgrammeRegistrationResult(false, 'flash.warning.programmeCancelledNoAssign');
        }

        return $this->programmeRepository->transactional(
            function () use ($programme, $participant): ProgrammeRegistrationResult {
                $lockedProgramme = $this->programmeRepository->getForUpdate($programme->id);
                if ($lockedProgramme === null || $lockedProgramme->isDeleted()) {
                    return new ProgrammeRegistrationResult(false, 'flash.error.programmeNotFound');
                }

                if ($this->programmeRegistrationRepository->findActive($programme, $participant) !== null) {
                    return new ProgrammeRegistrationResult(false, 'flash.warning.programmeAlreadyAssigned');
                }

                $this->createRegistration($programme, $participant);

                return new ProgrammeRegistrationResult(true, 'flash.success.programmeParticipantAssigned');
            },
        );
    }

    public function adminRemove(Programme $programme, Participant $participant): ProgrammeRegistrationResult
    {
        return $this->programmeRepository->transactional(
            function () use ($programme, $participant): ProgrammeRegistrationResult {
                $this->programmeRepository->getForUpdate($programme->id);
                $registration = $this->programmeRegistrationRepository->findActive($programme, $participant);
                if ($registration === null) {
                    return new ProgrammeRegistrationResult(false, 'flash.warning.programmeNotAssigned');
                }

                $this->programmeRegistrationRepository->softDelete($registration);

                return new ProgrammeRegistrationResult(true, 'flash.success.programmeParticipantRemoved');
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

    private function createRegistration(Programme $programme, Participant $participant): void
    {
        $registration = new ProgrammeRegistration();
        $registration->programme = $programme;
        $registration->participant = $participant;
        $this->programmeRegistrationRepository->persist($registration);
    }
}
