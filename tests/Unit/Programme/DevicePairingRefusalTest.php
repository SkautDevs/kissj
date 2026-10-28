<?php

declare(strict_types=1);

namespace Tests\Unit\Programme;

use kissj\Programme\Device\DevicePairingRefusal;
use PHPUnit\Framework\TestCase;

class DevicePairingRefusalTest extends TestCase
{
    public function testEachRefusalHasItsWarning(): void
    {
        self::assertSame(
            'flash.warning.devicePairingTooManyAttempts',
            DevicePairingRefusal::TooManyAttempts->flashKey(),
        );
        self::assertSame('flash.warning.devicePairingCodeInvalid', DevicePairingRefusal::InvalidCode->flashKey());
        self::assertSame('flash.warning.devicePairingCodeUsed', DevicePairingRefusal::CodeAlreadyUsed->flashKey());
    }
}
