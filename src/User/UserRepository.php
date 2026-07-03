<?php

declare(strict_types=1);

namespace kissj\User;

use Dibi\Row;
use kissj\Event\Event;
use kissj\Orm\Order;
use kissj\Orm\Repository;

/**
 * @method User get(int $userId)
 * @method User getOneBy(mixed[] $criteria)
 * @method User[] findBy(mixed[] $criteria, Order[] $orders = [])
 * @method User|null findOneBy(mixed[] $criteria, Order[] $orders = [])
 */
class UserRepository extends Repository
{
    public function getUserFromEmail(string $email, Event $event): User
    {
        return $this->getOneBy([
            'email' => $email,
            'event' => $event,
        ]);
    }

    public function findUserFromEmail(string $email, Event $event): ?User
    {
        return $this->findOneBy([
            'email' => $email,
            'event' => $event,
        ]);
    }

    public function isEmailUserExisting(string $email, Event $event): bool
    {
        return $this->isExisting([
            'email' => $email,
            'event' => $event,
        ]);
    }

    public function findSkautisUser(int $skautisId, Event $event): ?User
    {
        return $this->findOneBy([
            'login_type' => UserLoginType::Skautis->value,
            'skautis_id' => $skautisId,
            'event' => $event,
        ]);
    }

    public function findFirstCaseInsensitiveVariant(string $email, Event $event): ?User
    {
        $qb = $this->createFluent();
        $qb->where('LOWER(email) = LOWER(%s)', $email);
        $qb->where('email != %s', $email);
        $qb->where('event_id = %i', $event->id);
        $qb->orderBy('id');

        /** @var ?(Row&iterable<string, mixed>) $row */
        $row = $qb->fetch();

        if ($row === null) {
            return null;
        }

        /** @var User $user */
        $user = $this->createEntity($row);

        return $user;
    }
}
