<?php

declare(strict_types=1);

namespace kissj\Programme;

use kissj\Application\DateTimeUtils;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\Programme\DTO\ProgrammeApiImageDTO;
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
        private ProgrammeEnrolmentRepository $programmeEnrolmentRepository,
        private ParticipantRepository $participantRepository,
        private UserRepository $userRepository,
        private Metrics $metrics,
    ) {
    }

    public function enrol(Participant $participant, int $programmeId): ProgrammeEnrolmentResult
    {
        $result = $this->programmeRepository->transactional(
            function () use ($participant, $programmeId): ProgrammeEnrolmentResult {
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
                    return ProgrammeEnrolmentResult::failure(ProgrammeEnrolmentError::NotFound);
                }

                if ($programme->isAutoenrolled) {
                    return ProgrammeEnrolmentResult::failure(ProgrammeEnrolmentError::NotPossible);
                }

                if ($programme->isForRole($participant->getRoleOrFail()) === false) {
                    return ProgrammeEnrolmentResult::failure(ProgrammeEnrolmentError::WrongRole);
                }

                if ($this->programmeEnrolmentRepository->findActive($programme, $participant) !== null) {
                    return ProgrammeEnrolmentResult::failure(ProgrammeEnrolmentError::AlreadyEnrolled);
                }

                foreach ($this->programmeEnrolmentRepository->findActiveForParticipant($participant) as $existing) {
                    if ($programme->isExclusiveWith($existing->programme)) {
                        return ProgrammeEnrolmentResult::failure(ProgrammeEnrolmentError::Conflict);
                    }
                }

                $taken = $this->programmeEnrolmentRepository->countActiveForProgramme($programme);
                if ($taken >= $programme->capacity) {
                    return ProgrammeEnrolmentResult::failure(ProgrammeEnrolmentError::Full);
                }

                $this->createEnrolment($programme, $participant);

                return ProgrammeEnrolmentResult::success();
            },
        );

        $this->countOutcome($participant, $result, MetricName::ProgrammesEnrolled);

        return $result;
    }

    public function withdraw(Participant $participant, int $programmeId): ProgrammeEnrolmentResult
    {
        $refusal = $this->findSelfServiceRefusal($participant->getUserButNotNull());
        if ($refusal !== null) {
            $this->countOutcome($participant, $refusal, MetricName::ProgrammesWithdrawn);

            return $refusal;
        }

        $result = $this->programmeRepository->transactional(
            function () use ($participant, $programmeId): ProgrammeEnrolmentResult {
                $programme = $this->programmeRepository->findWithLock($programmeId);

                if (
                    $programme === null
                    || $programme->isDeleted()
                    || $programme->event->id !== $participant->getUserButNotNull()->event->id
                ) {
                    return ProgrammeEnrolmentResult::failure(ProgrammeEnrolmentError::NotFound);
                }

                if ($programme->isAutoenrolled) {
                    return ProgrammeEnrolmentResult::failure(ProgrammeEnrolmentError::NotPossible);
                }

                $enrolment = $this->programmeEnrolmentRepository->findActive($programme, $participant);
                if ($enrolment === null) {
                    return ProgrammeEnrolmentResult::failure(ProgrammeEnrolmentError::NotEnrolled);
                }

                $this->programmeEnrolmentRepository->softDelete($enrolment);

                return ProgrammeEnrolmentResult::success();
            },
        );

        $this->countOutcome($participant, $result, MetricName::ProgrammesWithdrawn);

        return $result;
    }

    public function adminAssign(Programme $programme, Participant $participant): ProgrammeEnrolmentResult
    {
        if ($programme->isAutoenrolled) {
            return ProgrammeEnrolmentResult::failure(ProgrammeEnrolmentError::AutoenrolledNoAssign);
        }

        return $this->programmeRepository->transactional(
            function () use ($programme, $participant): ProgrammeEnrolmentResult {
                $this->participantRepository->lock($participant);

                $user = $this->userRepository->get($participant->getUserButNotNull()->id);
                if ($user->status === UserStatus::Cancelled) {
                    return ProgrammeEnrolmentResult::failure(ProgrammeEnrolmentError::CancelledNoAssign);
                }

                $lockedProgramme = $this->programmeRepository->findWithLock($programme->id);
                if ($lockedProgramme === null || $lockedProgramme->isDeleted()) {
                    return ProgrammeEnrolmentResult::failure(ProgrammeEnrolmentError::NotFound);
                }

                if ($this->programmeEnrolmentRepository->findActive($programme, $participant) !== null) {
                    return ProgrammeEnrolmentResult::failure(ProgrammeEnrolmentError::AlreadyAssigned);
                }

                $this->createEnrolment($programme, $participant);

                return ProgrammeEnrolmentResult::success('flash.success.programmeParticipantAssigned');
            },
        );
    }

    public function adminRemove(Programme $programme, Participant $participant): ProgrammeEnrolmentResult
    {
        return $this->programmeRepository->transactional(
            function () use ($programme, $participant): ProgrammeEnrolmentResult {
                $this->programmeRepository->findWithLock($programme->id);
                $enrolment = $this->programmeEnrolmentRepository->findActive($programme, $participant);
                if ($enrolment === null) {
                    return ProgrammeEnrolmentResult::failure(ProgrammeEnrolmentError::NotAssigned);
                }

                $this->programmeEnrolmentRepository->softDelete($enrolment);

                return ProgrammeEnrolmentResult::success('flash.success.programmeParticipantRemoved');
            },
        );
    }

    public function deleteProgramme(Programme $programme): void
    {
        $this->programmeRepository->transactional(
            function () use ($programme): void {
                $this->programmeEnrolmentRepository->softDeleteAllActiveForProgramme($programme);
                $programme->deletedAt = DateTimeUtils::getDateTime();
                $this->programmeRepository->persist($programme);
            },
        );
    }

    public function freeAllProgrammes(Participant $participant): void
    {
        $this->programmeEnrolmentRepository->softDeleteAllActiveForParticipant($participant);
    }

    /**
     * @return list<ProgrammeEnrolment>
     */
    public function getDashboardEnrolments(Participant $participant): array
    {
        if ($participant->getUserButNotNull()->status !== UserStatus::Paid) {
            return [];
        }

        return $this->programmeEnrolmentRepository->findActiveForParticipant($participant);
    }

    /**
     * @return list<Programme>
     */
    public function getDashboardAutoenrolledProgrammes(Participant $participant): array
    {
        $user = $participant->getUserButNotNull();
        if ($user->status !== UserStatus::Paid) {
            return [];
        }

        return $this->programmeRepository->findActiveAutoenrolledForRole($user->event, $participant->getRoleOrFail());
    }

    /**
     * @param list<ProgrammeApiImageDTO> $images
     */
    public function presentProgramme(Programme $programme, array $images): ProgrammeApiProgrammeDTO
    {
        return new ProgrammeApiProgrammeDTO(
            $programme->id,
            $programme->name,
            $programme->section->id,
            $programme->description,
            $programme->getPlaceName(),
            $programme->programmePlace?->id,
            DateTimeUtils::formatAtom($programme->start),
            DateTimeUtils::formatAtom($programme->end),
            $programme->isAutoenrolled,
            array_map(
                static fn (ParticipantRole $role): string => $role->value,
                $programme->getTargetRoles(),
            ),
            $images,
        );
    }

    private function findSelfServiceRefusal(User $user): ?ProgrammeEnrolmentResult
    {
        if ($user->status !== UserStatus::Paid) {
            return ProgrammeEnrolmentResult::failure(ProgrammeEnrolmentError::NotPaid);
        }

        if ($user->event->isProgrammeEnrolmentOpen() === false) {
            return ProgrammeEnrolmentResult::failure(ProgrammeEnrolmentError::Closed);
        }

        return null;
    }

    private function countOutcome(
        Participant $participant,
        ProgrammeEnrolmentResult $result,
        MetricName $successMetric,
    ): void {
        $attributes = ['event' => $participant->getUserButNotNull()->event->slug];

        if ($result->error === null) {
            $this->metrics->count($successMetric, 1, $attributes);

            return;
        }

        $this->metrics->count(
            MetricName::ProgrammesEnrolmentRefused,
            1,
            [...$attributes, 'error' => $result->error->value, 'action' => $successMetric->value],
        );
    }

    private function createEnrolment(Programme $programme, Participant $participant): void
    {
        $enrolment = new ProgrammeEnrolment();
        $enrolment->programme = $programme;
        $enrolment->participant = $participant;
        $this->programmeEnrolmentRepository->persist($enrolment);
    }
}
