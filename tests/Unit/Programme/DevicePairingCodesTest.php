<?php

declare(strict_types=1);

namespace Tests\Unit\Programme;

use kissj\Programme\Device\DevicePairingCodes;
use PHPUnit\Framework\TestCase;

class DevicePairingCodesTest extends TestCase
{
    public function testUserCodeIsSixDigits(): void
    {
        for ($i = 0; $i < 20; $i++) {
            self::assertMatchesRegularExpression('/^\d{6}$/', DevicePairingCodes::generateUserCode());
        }
    }

    public function testDeviceCodeIs32Hex(): void
    {
        self::assertMatchesRegularExpression('/^[0-9a-f]{32}$/', DevicePairingCodes::generateDeviceCode());
    }

    public function testSessionTokenIs64Hex(): void
    {
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', DevicePairingCodes::generateSessionToken());
    }

    public function testTwoDrawsDiffer(): void
    {
        self::assertNotSame(DevicePairingCodes::generateDeviceCode(), DevicePairingCodes::generateDeviceCode());
        self::assertNotSame(DevicePairingCodes::generateSessionToken(), DevicePairingCodes::generateSessionToken());
    }

    public function testNormaliseStripsSpacesAndDashesOnly(): void
    {
        self::assertSame('483291', DevicePairingCodes::normaliseUserCode('483-291'));
        self::assertSame('483291', DevicePairingCodes::normaliseUserCode(' 483 291 '));
        self::assertSame('48a291', DevicePairingCodes::normaliseUserCode('48a291'));
    }
}
