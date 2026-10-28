<?php

declare(strict_types=1);

namespace kissj\Programme\Device;

use kissj\Orm\Order;
use kissj\Orm\Repository;

/**
 * @table device_session
 *
 * @method DeviceSession get(int $sessionId)
 * @method DeviceSession getOneBy(mixed[] $criteria)
 * @method DeviceSession[] findBy(mixed[] $criteria, Order[] $orders = [])
 * @method DeviceSession|null findOneBy(mixed[] $criteria, Order[] $orders = [])
 */
class DeviceSessionRepository extends Repository
{
    public function findByTokenHash(string $tokenHash): ?DeviceSession
    {
        return $this->findOneBy(['token_hash' => $tokenHash]);
    }
}
