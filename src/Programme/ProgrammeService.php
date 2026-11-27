<?php

declare(strict_types=1);

namespace kissj\Programme;

use kissj\Application\DateTimeUtils;

readonly class ProgrammeService
{
    public function __construct(
        private ProgrammeRepository $programmeRepository,
        private ProgrammeEnrolmentRepository $programmeEnrolmentRepository,
    ) {
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
}
