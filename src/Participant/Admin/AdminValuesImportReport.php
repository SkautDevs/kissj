<?php

declare(strict_types=1);

namespace kissj\Participant\Admin;

readonly class AdminValuesImportReport
{
    /**
     * @param list<AdminValuesImportProblem> $problems
     */
    public function __construct(
        public int $okCount,
        public array $problems,
    ) {
    }
}
