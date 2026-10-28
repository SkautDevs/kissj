<?php

declare(strict_types=1);

namespace kissj\Programme\Device;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use kissj\Event\Event;
use kissj\Orm\EntityDatetime;
use kissj\Participant\Participant;

/**
 * @property int                    $id
 * @property Event                  $event m:hasOne
 * @property string                 $deviceId
 * @property string                 $deviceCode
 * @property string                 $userCode
 * @property DevicePairingStatus    $status m:passThru(statusFromString|statusToString)
 * @property Participant|null       $participant m:hasOne
 * @property DeviceSession|null     $deviceSession m:hasOne
 * @property DateTimeInterface      $expiresAt m:passThru(dateFromString|dateToString)
 * @property DateTimeInterface|null $lastPolledAt m:passThru(dateFromString|dateToString)
 */
class DevicePairing extends EntityDatetime
{
    public const string LIFETIME = 'PT1H'; // 1 hour
    public const int POLL_INTERVAL_SECONDS = 5;
    private const string APPROVED_GRACE = 'PT1M'; // 1 minute
    private const int POLL_JITTER_SECONDS = 1;

    public function statusFromString(string $status): DevicePairingStatus
    {
        return DevicePairingStatus::from($status);
    }

    public function statusToString(DevicePairingStatus $status): string
    {
        return $status->value;
    }

    public function isExpired(DateTimeInterface $now): bool
    {
        $expiresAt = DateTimeImmutable::createFromInterface($this->expiresAt);
        if ($this->status === DevicePairingStatus::Approved || $this->status === DevicePairingStatus::Consumed) {
            $expiresAt = $expiresAt->add(new DateInterval(self::APPROVED_GRACE));
        }

        return $expiresAt <= $now;
    }

    public function isPolledTooSoon(DateTimeInterface $now): bool
    {
        if ($this->lastPolledAt === null) {
            return false;
        }

        $minimalGap = self::POLL_INTERVAL_SECONDS - self::POLL_JITTER_SECONDS;

        return $this->lastPolledAt->getTimestamp() + $minimalGap > $now->getTimestamp();
    }
}
