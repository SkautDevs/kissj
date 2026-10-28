<?php

declare(strict_types=1);

namespace kissj\Application;

readonly class JsonWarningDTO
{
    public function __construct(
        public string $translationKey,
        public string $translationMessage,
    ) {
    }
}
