<?php

declare(strict_types=1);

namespace kissj\User;

readonly class UserRegeneration
{
    private ?User $currentUser;

    public function __construct(private UserRepository $userRepository)
    {
        /** @var array<string, string|int> $userSession */
        $userSession = $_SESSION['user'] ?? [];

        $this->currentUser = $this->recreateUserFromSession($userSession);
    }

    public function getCurrentUser(): ?User
    {
        return $this->currentUser;
    }

    /**
     * @param array<string, string|int> $userSession
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
        if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
            $_SESSION['user'] = [];
        }

        $_SESSION['user']['id'] = $user->id;
    }
}
