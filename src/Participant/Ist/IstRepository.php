<?php

declare(strict_types=1);

namespace kissj\Participant\Ist;

use Dibi\Row;
use kissj\Event\Event;
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
    public function isIstExisting(string $email, Event $event): bool
    {
        $qb = $this->createFluent();

        $qb->where('participant.email = %s', $email);
        $qb->join('user')->as('u')->on('u.id = participant.user_id');
        $qb->where('u.event_id = %i', $event->id);

        /** @var ?Row $row */
        $row = $qb->fetch();

		return $row instanceof Row;
	}
}
