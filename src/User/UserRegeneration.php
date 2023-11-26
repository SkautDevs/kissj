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
     */
    private function recreateUserFromSession(array $userSession): ?User
    {
        if (!array_key_exists('id', $userSession)) {
            return null;
        }

        return $this->userRepository->findOneBy(['id' => $userSession['id']]);
    }

    public function saveUserIdIntoSession(User $user): void
    {
        $_SESSION['user']['id'] = $user->id;
    }
}
