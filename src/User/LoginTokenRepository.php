<?php

declare(strict_types=1);

namespace kissj\User;

use kissj\Orm\Repository;

/**
 * @method LoginToken[] findBy(mixed[] $criteria)
 * @method LoginToken|null findOneBy(mixed[] $criteria)
 * @method LoginToken getOneBy(mixed[] $criteria)
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
