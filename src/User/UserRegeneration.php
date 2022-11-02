<?php

declare(strict_types=1);

namespace kissj\User;

class UserRegeneration
{
    private readonly ?User $currentUser;

    public function __construct(private readonly UserRepository $userRepository)
    {
        $userSession = $_SESSION['user'] ?? [];

        $this->currentUser = $this->recreateUserFromSession($userSession);
    }

    public function getCurrentUser(): ?User
    {
        return $this->currentUser;
    }

    /**
     * @param string[] $userSession
     * @return User|null
     */
    private function recreateUserFromSession(array $userSession): ?User
    {
        if (!array_key_exists('id', $userSession)) {
            return null;
        }

        /** @var User|null $user */
        $user = $this->userRepository->findOneBy(['id' => $userSession['id']]);

        return $user;
    }

    public function saveUserIdIntoSession(User $user): void
    {
        $_SESSION['user']['id'] = $user->id;
    }
}
