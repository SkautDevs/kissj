<?php

declare(strict_types=1);

namespace kissj\Participant\Ist;

use kissj\Event\Event;
use kissj\Orm\Repository;

/**
 * @table participant
 *
 * @method Ist get(int $istId)
 * @method Ist[] findBy(mixed[] $criteria)
 * @method Ist|null findOneBy(mixed[] $criteria)
 * @method Ist getOneBy(mixed[] $criteria)
 */
class IstRepository extends Repository
{
}
