<?php

declare(strict_types=1);

namespace kissj\Participant\Ist;

use kissj\Orm\Order;
use kissj\Orm\Repository;

/**
 * @table participant
 *
 * @method Ist get(int $istId)
 * @method Ist getOneBy(mixed[] $criteria)
 * @method Ist[] findBy(mixed[] $criteria, Order[] $orders = [])
 * @method Ist|null findOneBy(mixed[] $criteria, Order[] $orders = [])
 */
class IstRepository extends Repository
{
}
