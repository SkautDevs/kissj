<?php

namespace kissj\User;

class UserRegeneration {
    private $userRepository;
    private $currentUser;

    public function __construct(UserRepository $userRepository, array $possibleUserSession) {
        $this->userRepository = $userRepository;

        if ($this->canRecreateUserFromSession($possibleUserSession)) {
            $this->currentUser = $this->recreateUserFromSession($possibleUserSession);
        } else {
            $this->currentUser = null;
        }
    }

    public function getGuestUser(): User {
        return new User();
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

    public function saveUserIdIntoSession(User $user) {
        $_SESSION['user']['id'] = $user->id;
    }
}
