<?php

declare(strict_types=1);

namespace Tests\Unit\Programme;

use kissj\Programme\ProgrammeRegistrationError;
use PHPUnit\Framework\TestCase;

class ProgrammeRegistrationErrorTest extends TestCase
{
    public function testEveryCaseHasAFlashKey(): void
    {
        foreach (ProgrammeRegistrationError::cases() as $error) {
            self::assertStringStartsWith('flash.', $error->flashKey());
        }
    }

    public function testApiCodesMatchTheContract(): void
    {
        $selfServiceErrors = [
            ProgrammeRegistrationError::NotPaid,
            ProgrammeRegistrationError::Closed,
            ProgrammeRegistrationError::NotFound,
            ProgrammeRegistrationError::NotPossible,
            ProgrammeRegistrationError::WrongRole,
            ProgrammeRegistrationError::AlreadyRegistered,
            ProgrammeRegistrationError::NotRegistered,
            ProgrammeRegistrationError::Conflict,
            ProgrammeRegistrationError::Full,
        ];

        self::assertSame(
            [
                'notPaid',
                'closed',
                'notFound',
                'notPossible',
                'wrongRole',
                'alreadyRegistered',
                'notRegistered',
                'conflict',
                'full',
            ],
            array_map(fn (ProgrammeRegistrationError $error): string => $error->value, $selfServiceErrors),
        );
    }
}
