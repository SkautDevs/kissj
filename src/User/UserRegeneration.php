<?php

namespace kissj\User;

class UserRegeneration {
    private ?User $currentUser;

    public function __construct(private UserRepository $userRepository) {
        $possibleUserSession = $_SESSION['user'] ?? [];

        if ($this->canRecreateUserFromSession($possibleUserSession)) {
            $this->currentUser = $this->recreateUserFromSession($possibleUserSession);
        } else {
            $this->currentUser = null;
        }
    }

    public function getCurrentUser(): ?User {
        return $this->currentUser;
    }

    private function canRecreateUserFromSession(array $possibleUserSession): bool {
        return $this->userRepository->isExisting(['id' => $possibleUserSession['id'] ?? null]);
    }

    private function recreateUserFromSession(array $userSession): User {
        return $this->userRepository->findOneBy(['id' => $userSession['id']]);
    }

    public function saveUserIdIntoSession(User $user): void {
        $_SESSION['user']['id'] = $user->id;
    }
}
