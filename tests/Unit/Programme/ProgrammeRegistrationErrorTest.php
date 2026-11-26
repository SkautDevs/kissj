<?php

declare(strict_types=1);

namespace Tests\Unit\Programme;

use kissj\Programme\ProgrammeRegistrationError;
use kissj\Programme\ProgrammeRegistrationResult;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ProgrammeRegistrationErrorTest extends TestCase
{
    public function testAdminErrorsHaveAFlashKey(): void
    {
        self::assertSame('flash.error.programmeNotFound', ProgrammeRegistrationError::NotFound->flashKey());
        self::assertSame(
            'flash.warning.programmePreregisteredNoAssign',
            ProgrammeRegistrationError::PreregisteredNoAssign->flashKey(),
        );
        self::assertSame(
            'flash.warning.programmeCancelledNoAssign',
            ProgrammeRegistrationError::CancelledNoAssign->flashKey(),
        );
        self::assertSame(
            'flash.warning.programmeAlreadyAssigned',
            ProgrammeRegistrationError::AlreadyAssigned->flashKey(),
        );
        self::assertSame('flash.warning.programmeNotAssigned', ProgrammeRegistrationError::NotAssigned->flashKey());
    }

    #[DataProvider('selfServiceOnlyErrors')]
    public function testSelfServiceOnlyErrorsHaveNoFlashKey(ProgrammeRegistrationError $error): void
    {
        $this->expectException(LogicException::class);

        $error->flashKey();
    }

    /**
     * @return iterable<string, array{ProgrammeRegistrationError}>
     */
    public static function selfServiceOnlyErrors(): iterable
    {
        yield 'notPaid' => [ProgrammeRegistrationError::NotPaid];
        yield 'closed' => [ProgrammeRegistrationError::Closed];
        yield 'notPossible' => [ProgrammeRegistrationError::NotPossible];
        yield 'wrongRole' => [ProgrammeRegistrationError::WrongRole];
        yield 'alreadyRegistered' => [ProgrammeRegistrationError::AlreadyRegistered];
        yield 'notRegistered' => [ProgrammeRegistrationError::NotRegistered];
        yield 'conflict' => [ProgrammeRegistrationError::Conflict];
        yield 'full' => [ProgrammeRegistrationError::Full];
    }

    public function testResultFlashKey(): void
    {
        self::assertSame('a.b', ProgrammeRegistrationResult::success('a.b')->flashKey());
        self::assertSame(
            'flash.error.programmeNotFound',
            ProgrammeRegistrationResult::failure(ProgrammeRegistrationError::NotFound)->flashKey(),
        );
        self::assertSame(
            ProgrammeRegistrationError::Full,
            ProgrammeRegistrationResult::failure(ProgrammeRegistrationError::Full)->error,
        );
    }

    public function testSuccessWithoutKeyHasNoFlashKey(): void
    {
        $this->expectException(LogicException::class);

        ProgrammeRegistrationResult::success()->flashKey();
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
