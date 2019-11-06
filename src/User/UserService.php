<?php

namespace kissj\User;

use kissj\Mailer\PhpMailerWrapper;
use kissj\Orm\Relation;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use PHPUnit\Framework\MockObject\RuntimeException;
use Slim\Router;

class UserService {
    private $router;
    private $userRepository;
    private $mailer;
    private $loginTokenRepository;
    private $participantRepository;

    public function __construct(
        LoginTokenRepository $loginTokenRepository,
        ParticipantRepository $participantRepository,
        UserRepository $userRepository,
        PhpMailerWrapper $mailer,
        Router $router
    ) {
        $this->loginTokenRepository = $loginTokenRepository;
        $this->participantRepository = $participantRepository;
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
        $this->router = $router;
    }

    public function isEmailExisting(string $email): bool {
        return $this->userRepository->isExisting(['email' => $email]);
    }

    public function registerUser(string $email): User {
        $user = new User();
        $user->email = $email;
        $this->userRepository->persist($user);

        return $user;
    }

    public function sendLoginTokenByMail(string $email): string {
        /** @var User $user */
        $user = $this->userRepository->findOneBy(['email' => $email]);
        $this->invalidateAllLoginTokens($user);

        // generate new token
        $loginToken = new LoginToken();
        $token = $this->generateTokenString();
        $loginToken->token = $token;
        $loginToken->user = $user;
        $loginToken->used = false;

        $this->loginTokenRepository->persist($loginToken);

        $link = $this->router->pathFor('loginWithToken', ['token' => $token]);
        $this->mailer->sendMailFromTemplate(
            $email,
            'link with login',
            'login-token',
            ['link' => $link, 'event' => $user->event]
        );

        return $token;
    }

    public function generateTokenString(): string {
        return md5(random_int(PHP_INT_MIN, PHP_INT_MAX));
    }

    public function isLoginTokenValid(string $loginToken): bool {
        $criteria = ['token' => $loginToken, 'used' => false];
        if (!$this->loginTokenRepository->isExisting($criteria)) {
            return false;
        }
        $lastToken = $this->loginTokenRepository->findOneBy(
            $criteria,
            ['created_at' => false]
        );
        if ($lastToken === null) {
            return false;
        }

        $lastValidTime = new \DateTime();
        $lastValidTime->modify('-15 minutes');

        return !($lastToken->createdAt < $lastValidTime);
    }

    public function getLoginTokenFromStringToken(string $token): LoginToken {
        return $this->loginTokenRepository->findOneBy(['token' => $token]);
    }

    public function getTokenForEmail(string $email): string {
        return $this->getTokenForUser($this->getUserFromEmail($email));
    }

    public function getTokenForUser(User $user): string {
        return $this->loginTokenRepository->findOneBy(['user' => $user])->token;
    }

    public function getUserFromEmail(string $email): User {
        return $this->userRepository->findOneBy(['email' => $email]);
    }

    public function logoutUser(): void {
        unset($_SESSION['user']);
    }

    public function invalidateAllLoginTokens(User $user): void {
        // invalidate all not yet used login tokens
        $existingTokens = $this->loginTokenRepository->findBy([$user, 'used' => false]);
        foreach ($existingTokens as $token) {
            $token->used = true;
            $this->loginTokenRepository->persist($token);
        }
    }

    public function setRole(User $user, string $role): void {
        if (!$this->isRoleValid($role)) {
            throw new RuntimeException('Role '.$role.' is not valid!');
        }

        $participant = new Participant();
        $participant->user = $user;
        $participant->role = $role;
        $this->participantRepository->persist($participant);

        $user->role = $role;
        $user->status = User::STATUS_OPEN;
        $this->userRepository->persist($user);
    }

    public function getClosedIstsCount(): int {
        return $this->userRepository->countBy([
            'role' => USER::ROLE_IST,
            //'event' => $this->eventName, // TODO fix
            'status' => new Relation(User::STATUS_OPEN, '!='),
        ]);
    }

    public function getClosedPatrolsCount(): int {
        return $this->userRepository->countBy([
            'role' => USER::ROLE_PATROL_LEADER,
            //'event' => $this->eventName, // TODO fix
            'status' => new Relation(User::STATUS_OPEN, '!='),
        ]);
    }

    protected function isRoleValid(string $role): bool {
        return in_array($role, [User::ROLE_IST, User::ROLE_PATROL_LEADER, User::ROLE_GUEST], true);
    }

    public function closeRegistration(User $user): User {
        $user->status = User::STATUS_CLOSED;
        $this->userRepository->persist($user);

        return $user;
    }

    public function approveRegistration(User $user): User {
        $user->status = User::STATUS_APPROVED;
        $this->userRepository->persist($user);

        return $user;
    }

    public function payRegistration(User $user): User {
        $user->status = User::STATUS_PAID;
        $this->userRepository->persist($user);

        return $user;
    }
}
