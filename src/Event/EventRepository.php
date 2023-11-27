<?php

declare(strict_types=1);

namespace kissj\Event;

use kissj\Application\DateTimeUtils;
use kissj\Orm\Relation;
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
            'end_day' => new Relation(DateTimeUtils::getDateTime('-1 month')->format(DATE_ATOM), '>'),
        ]);
    }

    /**
     * @return Event[]
     */
    public function findActiveNontestAutopaymentsOnEvents(): array
    {
        return $this->findBy([
            'testing_site' => false,
            'automatic_payment_pairing' => true,
            'start_registration' => new Relation(DateTimeUtils::getDateTime()->format(DATE_ATOM), '<'),
            'end_day' => new Relation(DateTimeUtils::getDateTime('-3 month')->format(DATE_ATOM), '>'),
        ]);
    }

	public function findBySlug(string $eventSlug): ?Event
	{
		return $this->findOneBy(['slug' => $eventSlug]);
	}
}
