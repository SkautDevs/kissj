<?php

declare(strict_types=1);

namespace kissj\Participant\Guest;

use kissj\Orm\Order;
use kissj\Orm\Repository;

/**
 * @table participant
 *
 * @method Guest get(int $istId)
 * @method Guest getOneBy(mixed[] $criteria)
 * @method Guest[] findBy(mixed[] $criteria, Order[] $orders = [])
 * @method Guest|null findOneBy(mixed[] $criteria, Order[] $orders = [])
 */
class GuestRepository extends Repository
{
}
