<?php

declare(strict_types=1);

namespace kissj\Participant\Ist;

use DateTimeImmutable;
use Dibi\Row;
use kissj\Event\Event;
use kissj\Orm\Order;
use kissj\Orm\Repository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRole;
use kissj\Payment\Payment;
use kissj\Payment\PaymentStatus;

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
    public function findIst(string $email, Event $event): ?Ist
    {
        $qb = $this->createFluent();

        $qb->where('participant.email = %s', $email);
        $qb->where('participant.role = ist');
        $qb->join('user')->as('u')->on('u.id = participant.user_id');
        $qb->where('u.event_id = %i', $event->id);


        /** @var Row $row */
        $row = $qb->fetch();
        /** @var ?Ist $ist */
        $ist = $this->createEntity($row);

        return $ist;
    }
}
