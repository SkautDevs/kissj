<?php

declare(strict_types=1);

namespace kissj\Participant\Admin;

readonly class AdminValuesImportProblem
{
    public function __construct(
        public int $lineNumber,
        public string $name,
        public AdminValuesImportProblemType $type,
    ) {
    }
}
