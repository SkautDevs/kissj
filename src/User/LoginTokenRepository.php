<?php

declare(strict_types=1);

namespace kissj\User;

use kissj\Orm\Order;
use kissj\Orm\Repository;

/**
 * @method LoginToken get(int $loginTokenId)
 * @method LoginToken getOneBy(mixed[] $criteria)
 * @method LoginToken[] findBy(mixed[] $criteria, Order[] $orders = [])
 * @method LoginToken|null findOneBy(mixed[] $criteria, Order[] $orders = [])
 */
class LoginTokenRepository extends Repository
{
    /**
     * @return LoginToken[]
     */
    public function findAllNonusedTokens(User $user): array
    {
        return $this->findBy(['u' => $user, 'used' => false]);
    }

    public function getTokenForUser(User $user): string
    {
        return $this->getOneBy(['user' => $user])->token;
    }
}
