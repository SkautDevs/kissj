<?php declare(strict_types=1);

namespace kissj\User;

use kissj\Event\Event;
use kissj\Orm\Repository;

/**
 * @method User[] findBy(mixed[] $criteria)
 * @method User|null findOneBy(mixed[] $criteria)
 * @method User getOneBy(mixed[] $criteria)
 */
class UserRepository extends Repository
{
    public function getUserFromEmailEvent(string $email, Event $event): User
    {
        return $this->getOneBy(['email' => $email, 'event' => $event]);
    }

    public function isUserExisting(string $email, Event $event): bool
    {
        return $this->isExisting(['email' => $email, 'event' => $event]);
    }
}
