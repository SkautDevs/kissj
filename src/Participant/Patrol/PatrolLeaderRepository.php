<?php

declare(strict_types=1);

namespace kissj\Participant\Patrol;

use kissj\Orm\Order;
use kissj\Orm\Repository;

/**
 * @table participant
 *
 * @method PatrolLeader get(int $patrolLeaderId)
 * @method PatrolLeader getOneBy(mixed[] $criteria)
 * @method PatrolLeader[] findBy(mixed[] $criteria, Order[] $orders = [])
 * @method PatrolLeader|null findOneBy(mixed[] $criteria, Order[] $orders = [])
 */
class PatrolLeaderRepository extends Repository
{
}
