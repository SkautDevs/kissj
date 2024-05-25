<?php

declare(strict_types=1);

namespace kissj\User;

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

}
