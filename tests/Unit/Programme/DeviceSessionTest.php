<?php

declare(strict_types=1);

namespace Tests\Unit\Programme;

use kissj\Application\DateTimeUtils;
use kissj\Programme\Device\DeviceSession;
use PHPUnit\Framework\TestCase;

class DeviceSessionTest extends TestCase
{
    public function testExtensionIsDueOnlyWhenMoreThanADayShort(): void
    {
        $now = DateTimeUtils::getDateTime('2027-01-10 12:00:00');
        $session = new DeviceSession();

        $session->expiresAt = $now->modify('+6 months -2 days');
        self::assertTrue($session->isExtensionDue($now));

        $session->expiresAt = $now->modify('+6 months -12 hours');
        self::assertFalse($session->isExtensionDue($now));

        $session->expiresAt = $now->modify('+6 months');
        self::assertFalse($session->isExtensionDue($now));
    }

    public function testExtendSetsSixMonthsFromNow(): void
    {
        $now = DateTimeUtils::getDateTime('2027-01-10 12:00:00');
        $session = new DeviceSession();
        $session->expiresAt = $now->modify('+1 day');

        $session->extend($now);

        self::assertEquals($now->modify('+6 months'), $session->expiresAt);
    }

    public function testIsExpiredAtTheBoundary(): void
    {
        $now = DateTimeUtils::getDateTime('2027-01-10 12:00:00');
        $session = new DeviceSession();

        $session->expiresAt = $now;
        self::assertTrue($session->isExpired($now));

        $session->expiresAt = $now->modify('+1 second');
        self::assertFalse($session->isExpired($now));
    }

    public function testHashTokenIsSha256Hex(): void
    {
        $hash = DeviceSession::hashToken('some-token');

        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $hash);
        self::assertSame(hash('sha256', 'some-token'), $hash);
    }
}
