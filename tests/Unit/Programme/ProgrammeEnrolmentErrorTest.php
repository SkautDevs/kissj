<?php

declare(strict_types=1);

namespace Tests\Unit\Programme;

use kissj\Programme\ProgrammeEnrolmentError;
use kissj\Programme\ProgrammeEnrolmentResult;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ProgrammeEnrolmentErrorTest extends TestCase
{
    public function testAdminErrorsHaveAFlashKey(): void
    {
        self::assertSame('flash.error.programmeNotFound', ProgrammeEnrolmentError::NotFound->flashKey());
        self::assertSame(
            'flash.warning.programmeAutoenrolledNoAssign',
            ProgrammeEnrolmentError::AutoenrolledNoAssign->flashKey(),
        );
        self::assertSame(
            'flash.warning.programmeCancelledNoAssign',
            ProgrammeEnrolmentError::CancelledNoAssign->flashKey(),
        );
        self::assertSame(
            'flash.warning.programmeAlreadyAssigned',
            ProgrammeEnrolmentError::AlreadyAssigned->flashKey(),
        );
        self::assertSame('flash.warning.programmeNotAssigned', ProgrammeEnrolmentError::NotAssigned->flashKey());
    }

    #[DataProvider('selfServiceOnlyErrors')]
    public function testSelfServiceOnlyErrorsHaveNoFlashKey(ProgrammeEnrolmentError $error): void
    {
        $this->expectException(LogicException::class);

        $error->flashKey();
    }

    /**
     * @return iterable<string, array{ProgrammeEnrolmentError}>
     */
    public static function selfServiceOnlyErrors(): iterable
    {
        yield 'notPaid' => [ProgrammeEnrolmentError::NotPaid];
        yield 'closed' => [ProgrammeEnrolmentError::Closed];
        yield 'notPossible' => [ProgrammeEnrolmentError::NotPossible];
        yield 'wrongRole' => [ProgrammeEnrolmentError::WrongRole];
        yield 'alreadyEnrolled' => [ProgrammeEnrolmentError::AlreadyEnrolled];
        yield 'notEnrolled' => [ProgrammeEnrolmentError::NotEnrolled];
        yield 'conflict' => [ProgrammeEnrolmentError::Conflict];
        yield 'full' => [ProgrammeEnrolmentError::Full];
    }

    public function testResultFlashKey(): void
    {
        self::assertSame('a.b', ProgrammeEnrolmentResult::success('a.b')->flashKey());
        self::assertSame(
            'flash.error.programmeNotFound',
            ProgrammeEnrolmentResult::failure(ProgrammeEnrolmentError::NotFound)->flashKey(),
        );
        self::assertSame(
            ProgrammeEnrolmentError::Full,
            ProgrammeEnrolmentResult::failure(ProgrammeEnrolmentError::Full)->error,
        );
    }

    public function testSuccessWithoutKeyHasNoFlashKey(): void
    {
        $this->expectException(LogicException::class);

        ProgrammeEnrolmentResult::success()->flashKey();
    }

    public function testApiCodesMatchTheContract(): void
    {
        $selfServiceErrors = [
            ProgrammeEnrolmentError::NotPaid,
            ProgrammeEnrolmentError::Closed,
            ProgrammeEnrolmentError::NotFound,
            ProgrammeEnrolmentError::NotPossible,
            ProgrammeEnrolmentError::WrongRole,
            ProgrammeEnrolmentError::AlreadyEnrolled,
            ProgrammeEnrolmentError::NotEnrolled,
            ProgrammeEnrolmentError::Conflict,
            ProgrammeEnrolmentError::Full,
        ];

        self::assertSame(
            [
                'notPaid',
                'closed',
                'notFound',
                'notPossible',
                'wrongRole',
                'alreadyEnrolled',
                'notEnrolled',
                'conflict',
                'full',
            ],
            array_map(fn (ProgrammeEnrolmentError $error): string => $error->value, $selfServiceErrors),
        );
    }
}
