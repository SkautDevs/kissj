<?php

declare(strict_types=1);

namespace kissj\Programme\DTO;

readonly class ProgrammeApiParticipantIdsDTO
{
    /**
     * @param list<int> $participantIds
     */
    public function __construct(
        public array $participantIds,
    ) {
    }
}
