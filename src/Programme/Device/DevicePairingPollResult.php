<?php

declare(strict_types=1);

namespace kissj\Programme\Device;

use DateTimeInterface;
use LogicException;

readonly class DevicePairingPollResult
{
    private function __construct(
        public DevicePairingPollStatus $status,
        public ?string $sessionToken,
        public ?DateTimeInterface $sessionExpiresAt,
    ) {
    }

    public static function withoutSession(DevicePairingPollStatus $status): self
    {
        if ($status === DevicePairingPollStatus::Approved) {
            throw new LogicException('Approved poll result needs a session, use approved()');
        }

        return new self($status, null, null);
    }

    public static function approved(string $sessionToken, DateTimeInterface $sessionExpiresAt): self
    {
        return new self(DevicePairingPollStatus::Approved, $sessionToken, $sessionExpiresAt);
    }
}
