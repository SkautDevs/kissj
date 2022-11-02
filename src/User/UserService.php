<?php

declare(strict_types=1);

namespace kissj\User;

use kissj\Event\Event;
use kissj\Mailer\PhpMailerWrapper;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;

class UserService
{
    public function __construct(
        private readonly LoginTokenRepository $loginTokenRepository,
        private readonly ParticipantRepository $participantRepository,
        private readonly UserRepository $userRepository,
        private readonly PhpMailerWrapper $mailer,
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
        $lastToken = $this->loginTokenRepository->findOneBy(['token' => $loginToken, 'used' => false]);
        if ($lastToken === null) {
            return false;
        }

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
        return $this->loginTokenRepository->getTokenForUser(
            $this->userRepository->getUserFromEmailEvent($email, $event)
        );
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
        $participantRole = ParticipantRole::from($role);

        $participant = new Participant();
        $participant->user = $user;
        $participant->role = $participantRole;
        $this->participantRepository->persist($participant);

        $user->role = UserRole::Participant;
        $user->status = UserStatus::Open;
        $this->userRepository->persist($user);
    }

    public function setUserOpen(User $user): User
    {
        $user->status = UserStatus::Open;
        $this->userRepository->persist($user);

        return $user;
    }

    public function setUserClosed(User $user): User
    {
        $user->status = UserStatus::Closed;
        $this->userRepository->persist($user);

        return $user;
    }

    public function setUserApproved(User $user): User
    {
        $user->status = UserStatus::Approved;
        $this->userRepository->persist($user);

        return $user;
    }

    public function setUserPaid(User $user): User
    {
        $user->status = UserStatus::Paid;
        $this->userRepository->persist($user);

        return $user;
    }
}
