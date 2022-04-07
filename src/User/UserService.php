<?php

declare(strict_types=1);

namespace kissj\User;

use kissj\Event\Event;
use kissj\Mailer\PhpMailerWrapper;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use PHPUnit\Framework\MockObject\RuntimeException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;

class UserService
{
    public function __construct(
        private LoginTokenRepository $loginTokenRepository,
        private ParticipantRepository $participantRepository,
        private UserRepository $userRepository,
        private PhpMailerWrapper $mailer,
    ) {
    }

    public function registerUser(string $email, Event $event): User
    {
        $user = new User();
        $user->email = $email;
        $user->event = $event;
        $this->userRepository->persist($user);

        return $user;
    }

    public function sendLoginTokenByMail(string $email, Request $request, Event $event): string
    {
        $user = $this->userRepository->getUserFromEmailEvent($email, $event);
        $this->invalidateAllLoginTokens($user);

        // generate new token
        $loginToken = new LoginToken();
        $token = $this->generateTokenString();
        $loginToken->token = $token;
        $loginToken->user = $user;
        $loginToken->used = false;

        $this->loginTokenRepository->persist($loginToken);

        // need to use full route
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $fullLink = $routeParser->fullUrlFor(
            $request->getUri(),
            'loginWithToken',
            ['token' => $token, 'eventSlug' => $event->slug]
        );
        $this->mailer->sendLoginToken($user, $fullLink);

        return $token;
    }

    public function generateTokenString(): string
    {
        return md5((string)random_int(PHP_INT_MIN, PHP_INT_MAX));
    }

    public function isLoginTokenValid(string $loginToken): bool
    {
        $criteria = ['token' => $loginToken, 'used' => false];
        if (!$this->loginTokenRepository->isExisting($criteria)) {
            return false;
        }

        /** @var LoginToken $lastToken */
        $lastToken = $this->loginTokenRepository->getOneBy(
            $criteria,
            ['created_at' => false]
        );

        $lastValidTime = new \DateTime();
        $lastValidTime->modify('-24 hours');

        return $lastToken->createdAt > $lastValidTime;
    }

    public function getLoginTokenFromStringToken(string $token): LoginToken
    {
        /** @var LoginToken $loginToken */
        $loginToken = $this->loginTokenRepository->findOneBy(['token' => $token]);

        return $loginToken;
    }

    public function getTokenForEmail(string $email, Event $event): string
    {
        return $this->getTokenForUser($this->userRepository->getUserFromEmailEvent($email, $event));
    }

    public function getTokenForUser(User $user): string
    {
        /** @var LoginToken $loginToken */
        $loginToken = $this->loginTokenRepository->findOneBy(['user' => $user]);

        return $loginToken->token;
    }

    public function logoutUser(): void
    {
        unset($_SESSION['user']);
    }

    public function invalidateAllLoginTokens(User $user): void
    {
        foreach ($this->loginTokenRepository->findAllNonusedTokens($user) as $token) {
            $token->used = true;
            $this->loginTokenRepository->persist($token);
        }
    }

    public function setRole(User $user, string $role): void
    {
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

    protected function isRoleValid(string $role): bool
    {
        // TODO check if role is valid for specific event
        $allowedRoles = [
            User::ROLE_IST,
            User::ROLE_PATROL_LEADER,
            User::ROLE_TROOP_LEADER,
            User::ROLE_TROOP_PARTICIPANT,
            User::ROLE_GUEST,
        ];

        return in_array($role, $allowedRoles, true);
    }

    public function openRegistration(User $user): User
    {
        $user->status = User::STATUS_OPEN;
        $this->userRepository->persist($user);

        return $user;
    }

    public function closeRegistration(User $user): User
    {
        $user->status = User::STATUS_CLOSED;
        $this->userRepository->persist($user);

        return $user;
    }

    public function approveRegistration(User $user): User
    {
        $user->status = User::STATUS_APPROVED;
        $this->userRepository->persist($user);

        return $user;
    }

    public function payRegistration(User $user): User
    {
        $user->status = User::STATUS_PAID;
        $this->userRepository->persist($user);

        return $user;
    }
}
