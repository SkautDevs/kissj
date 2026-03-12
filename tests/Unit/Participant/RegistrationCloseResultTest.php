<?php

declare(strict_types=1);

namespace Tests\Unit\Participant;

use kissj\Participant\RegistrationCloseResult;
use PHPUnit\Framework\TestCase;

class RegistrationCloseResultTest extends TestCase
{
    public function testStartCheckingIsValid(): void
    {
        $result = RegistrationCloseResult::startChecking();
        self::assertTrue($result->isValid);
        self::assertEmpty($result->warnings);
    }

    public function testWithWarningMakesResultInvalid(): void
    {
        $result = RegistrationCloseResult::startChecking()
            ->withWarning('flash.warning.noLock')
            ->withWarning('flash.warning.fullRegistration');

        self::assertFalse($result->isValid);
        self::assertCount(2, $result->warnings);
    }

    public function testWithWarningPreservesParams(): void
    {
        $result = RegistrationCloseResult::startChecking()
            ->withWarning('flash.warning.test', ['%name%' => 'John']);

        self::assertSame('flash.warning.test', $result->warnings[0]['key']);
        self::assertSame(['%name%' => 'John'], $result->warnings[0]['params']);
    }

    public function testDuplicateWarningKeysArePreserved(): void
    {
        $result = RegistrationCloseResult::startChecking()
            ->withWarning('flash.warning.fullRegistration')
            ->withWarning('flash.warning.fullRegistration');

        self::assertCount(2, $result->warnings);
    }
}
