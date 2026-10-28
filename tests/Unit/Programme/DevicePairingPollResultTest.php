<?php

declare(strict_types=1);

namespace Tests\Unit\Programme;

use kissj\Application\DateTimeUtils;
use kissj\Programme\Device\DevicePairingPollResult;
use kissj\Programme\Device\DevicePairingPollStatus;
use LogicException;
use PHPUnit\Framework\TestCase;

class DevicePairingPollResultTest extends TestCase
{
    public function testApprovedCarriesSession(): void
    {
        $expiresAt = DateTimeUtils::getDateTime('2027-01-10 12:00:00');

        $result = DevicePairingPollResult::approved('token', $expiresAt);

        self::assertSame('approved', $result->status->value);
        self::assertSame('token', $result->sessionToken);
        self::assertSame($expiresAt, $result->sessionExpiresAt);
    }

    public function testApprovedWithoutSessionIsRefused(): void
    {
        $this->expectException(LogicException::class);

        DevicePairingPollResult::withoutSession(DevicePairingPollStatus::Approved);
    }
}
