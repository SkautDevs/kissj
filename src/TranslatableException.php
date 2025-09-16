<?php

declare(strict_types=1);

namespace kissj;

use Exception;

class TranslatableException extends Exception
{
    public function __construct(
        public readonly string $translationKey,
        public readonly int $httpStatus = 400,
    ) {
        parent::__construct($translationKey);
    }
}
