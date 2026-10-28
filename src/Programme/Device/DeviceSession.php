<?php

declare(strict_types=1);

namespace kissj\Programme\Device;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use kissj\Orm\EntityDatetime;
use kissj\Participant\Participant;

/**
 * @property int               $id
 * @property Participant       $participant m:hasOne
 * @property string            $tokenHash
 * @property DateTimeInterface $expiresAt m:passThru(dateFromString|dateToString)
 */
class DeviceSession extends EntityDatetime
{
    private const string LIFETIME = 'P6M'; // 6 months
    private const string EXTENSION_SLACK = 'P1D'; // 1 day

    public function isExpired(DateTimeInterface $now): bool
    {
        return $this->expiresAt <= $now;
    }

    public function isExtensionDue(DateTimeInterface $now): bool
    {
        $threshold = $this->lifetimeEnd($now)->sub(new DateInterval(self::EXTENSION_SLACK));

        return $this->expiresAt < $threshold;
    }

    public function extend(DateTimeInterface $now): void
    {
        $this->expiresAt = $this->lifetimeEnd($now);
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    private function lifetimeEnd(DateTimeInterface $now): DateTimeImmutable
    {
        return DateTimeImmutable::createFromInterface($now)->add(new DateInterval(self::LIFETIME));
    }
}
