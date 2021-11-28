<?php

namespace kissj\User;

use kissj\Event\Event;
use kissj\Orm\Repository;

class UserRepository extends Repository
{
    public function getUserFromEmailEvent(string $email, Event $event): User
    {
        /** @var User $user */
        $user = $this->findOneBy(['email' => $email, 'event' => $event]);

        return $user;
    }

    public function isUserExisting(string $email, Event $event): bool
    {
        return $this->isExisting(['email' => $email, 'event' => $event]);
    }
}
