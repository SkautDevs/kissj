<?php declare(strict_types=1);

namespace kissj\User;

use kissj\Orm\Repository;

class LoginTokenRepository extends Repository
{
    /**
     * @param User $user
     * @return LoginToken[]
     */
    public function findAllNonusedTokens(User $user): array
    {
        /** @var LoginToken[] $loginTokens */
        $loginTokens = $this->findBy(['u' => $user, 'used' => false]);

        return $loginTokens;
    }
}
