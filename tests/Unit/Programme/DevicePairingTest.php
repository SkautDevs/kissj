<?php

declare(strict_types=1);

namespace Tests\Unit\Programme;

use kissj\Application\DateTimeUtils;
use kissj\Programme\Device\DevicePairing;
use kissj\Programme\Device\DevicePairingStatus;
use PHPUnit\Framework\TestCase;

class DevicePairingTest extends TestCase
{
    public function testApprovedPairingStaysRedeemableForOneMinuteAfterExpiry(): void
    {
        $now = DateTimeUtils::getDateTime('2027-01-10 12:00:00');
        $pairing = new DevicePairing();
        $pairing->status = DevicePairingStatus::Approved;

        $pairing->expiresAt = $now->modify('-30 seconds');
        self::assertFalse($pairing->isExpired($now));

        $pairing->expiresAt = $now->modify('-1 minute');
        self::assertTrue($pairing->isExpired($now));
    }

    public function testPendingPairingExpiresAtItsExpiry(): void
    {
        $now = DateTimeUtils::getDateTime('2027-01-10 12:00:00');
        $pairing = new DevicePairing();
        $pairing->status = DevicePairingStatus::Pending;

        $pairing->expiresAt = $now->modify('+1 second');
        self::assertFalse($pairing->isExpired($now));

        $pairing->expiresAt = $now;
        self::assertTrue($pairing->isExpired($now));
    }

    public function testPollOneSecondEarlierThanIntervalIsTolerated(): void
    {
        $now = DateTimeUtils::getDateTime('2027-01-10 12:00:00');
        $pairing = new DevicePairing();

        $pairing->lastPolledAt = $now->modify('-4 seconds');
        self::assertFalse($pairing->isPolledTooSoon($now));

        $pairing->lastPolledAt = $now->modify('-3 seconds');
        self::assertTrue($pairing->isPolledTooSoon($now));
    }
}
