<?php

declare(strict_types=1);

namespace kissj\Programme\Device;

use DateTimeInterface;
use Dibi\Drivers\PostgreDriver;
use Dibi\Row;
use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Orm\Order;
use kissj\Orm\Relation;
use kissj\Orm\Repository;

/**
 * @table device_pairing
 *
 * @method DevicePairing get(int $pairingId)
 * @method DevicePairing getOneBy(mixed[] $criteria)
 * @method DevicePairing[] findBy(mixed[] $criteria, Order[] $orders = [])
 * @method DevicePairing|null findOneBy(mixed[] $criteria, Order[] $orders = [])
 */
class DevicePairingRepository extends Repository
{
    public function findPendingByUserCode(string $userCode, Event $event, DateTimeInterface $now): ?DevicePairing
    {
        return $this->findOneBy([
            'event' => $event,
            'user_code' => $userCode,
            'status' => DevicePairingStatus::Pending->value,
            'expires_at' => new Relation(DateTimeUtils::formatAtom($now), '>'),
        ]);
    }

    public function existsCreatedAfter(string $userCode, Event $event, DateTimeInterface $after): bool
    {
        return $this->isExisting([
            'event' => $event,
            'user_code' => $userCode,
            'created_at' => new Relation(DateTimeUtils::formatAtom($after), '>'),
        ]);
    }

    public function findByDeviceCode(string $deviceCode, Event $event): ?DevicePairing
    {
        return $this->findOneBy(['device_code' => $deviceCode, 'event' => $event]);
    }

    public function getForUpdate(int $pairingId): ?DevicePairing
    {
        $lockClause = $this->connection->getDriver() instanceof PostgreDriver ? ' FOR UPDATE' : '';
        $row = $this->connection->fetch(
            'SELECT * FROM %n WHERE id = %i' . $lockClause,
            $this->getTable(),
            $pairingId,
        );
        if ($row === null) {
            return null;
        }

        /** @var Row&iterable<string, mixed> $row */
        /** @var DevicePairing $pairing */
        $pairing = $this->createEntity($row);

        return $pairing;
    }

    public function countPendingUnexpired(Event $event, DateTimeInterface $now): int
    {
        return $this->countBy([
            'event' => $event,
            'status' => DevicePairingStatus::Pending->value,
            'expires_at' => new Relation(DateTimeUtils::formatAtom($now), '>'),
        ]);
    }

    public function lockForEvent(Event $event): void
    {
        if ($this->connection->getDriver() instanceof PostgreDriver === false) {
            return;
        }

        $this->connection->query("SELECT pg_advisory_xact_lock(hashtext('device_pairing'), %i)", $event->id);
    }

    public function replacePendingOfDevice(Event $event, string $deviceId): void
    {
        $this->connection->update($this->getTable(), ['status' => DevicePairingStatus::Replaced->value])
            ->where('event_id = %i', $event->id)
            ->where('device_id = %s', $deviceId)
            ->where('status = %s', DevicePairingStatus::Pending->value)
            ->execute();
    }

    public function deletePending(Event $event): int
    {
        $this->connection->delete($this->getTable())
            ->where('event_id = %i', $event->id)
            ->where('status = %s', DevicePairingStatus::Pending->value)
            ->execute();

        return $this->connection->getAffectedRows();
    }
}
