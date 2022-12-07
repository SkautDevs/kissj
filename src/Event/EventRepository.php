<?php

declare(strict_types=1);

namespace kissj\Event;

use kissj\Orm\Repository;

/**
 * @method Event[] findAll()
 * @method Event[] findBy(mixed[] $criteria)
 * @method Event|null findOneBy(mixed[] $criteria)
 * @method Event get(int $id)
 * @method Event getOneBy(mixed[] $criteria)
 */
class EventRepository extends Repository
{
    /**
     * @return Event[]
     */
    public function findActiveNontestEvents(): array
    {
        return $this->findBy([
            'testing_site' => false,
            // TODO start and end registration dates
        ]);
    }
}
