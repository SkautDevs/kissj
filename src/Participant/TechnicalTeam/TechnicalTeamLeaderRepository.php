<?php

declare(strict_types=1);

namespace kissj\Participant\TechnicalTeam;

use kissj\Orm\Order;
use kissj\Orm\Repository;

/**
 * @table participant
 *
 * @method TechnicalTeamLeader get(int $technicalTeamLeaderId)
 * @method TechnicalTeamLeader getOneBy(mixed[] $criteria)
 * @method TechnicalTeamLeader[] findBy(mixed[] $criteria, Order[] $orders = [])
 * @method TechnicalTeamLeader|null findOneBy(mixed[] $criteria, Order[] $orders = [])
 */
class TechnicalTeamLeaderRepository extends Repository
{
}
