<?php

declare(strict_types=1);

namespace kissj\User;

use DateTimeImmutable;
use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Mailer\Mailer;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\Payment\Payment;
use kissj\Payment\PaymentRepository;
use kissj\Payment\PaymentStatus;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;

readonly class UserService
{
    public function __construct(
        private LoginTokenRepository $loginTokenRepository,
        private ParticipantRepository $participantRepository,
        private UserRepository $userRepository,
        private PaymentRepository $paymentRepository,
        private Mailer $mailer,
    ) {
    }

    public function registerEmailUser(string $email, Event $event): User
    {
        $user = new User();
        $user->loginType = UserLoginType::Email;
        $user->email = $email;
        $user->event = $event;
        $this->userRepository->persist($user);

        return $user;
    }

    public function registerSkautisUser(int $skautisId, bool $hasMembership, string $email, Event $event): User
    {
        $user = new User();
        $user->loginType = UserLoginType::Skautis;
        $user->skautisId = $skautisId;
        $user->skautisHasMembership = $hasMembership;
        $user->email = $email;
        $user->event = $event;
        $this->userRepository->persist($user);

        return $user;
    }

    public function sendLoginTokenByMail(string $email, Request $request, Event $event): string
    {
        $user = $this->userRepository->getUserFromEmail($email, $event);
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

        return $lastToken->createdAt > DateTimeUtils::getDateTime('now - 24 hours');
    }

    public function getLoginTokenFromStringToken(string $token): LoginToken
    {
        return $this->loginTokenRepository->getOneBy(['token' => $token]);
    }

    public function getTokenForEmail(string $email, Event $event): string
    {
        return $this->loginTokenRepository->getTokenForUser(
            $this->userRepository->getUserFromEmail($email, $event),
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

    public function createParticipantSetRole(User $user, string $role): Participant
    {
        $participantRole = ParticipantRole::from($role);

        $participant = new Participant();
        $participant->user = $user;
        $participant->role = $participantRole;
        $this->participantRepository->persist($participant);

        $user->role = UserRole::Participant; // TODO move into entity creation (simple)
        $user->status = UserStatus::Open;
        $this->userRepository->persist($user);

        return $participant;
    }

    /**
     * @param array<string> $preferredPosition
     */
    public function createSkautisUserParticipantPayment(
        Event $event,
        int $skautisId,
        string $email,
        UserStatus $userStatus,
        ParticipantRole $participantRole,
        string $contingent,
        string $firstName,
        string $lastName,
        string $nickname,
        string $permanentResidence,
        string $telephoneNumber,
        string $scoutUnit,
        DateTimeImmutable $birthDate,
        string $healthProblems,
        string $medicaments,
        string $psychicalHealthProblems,
        string $foodPreferences,
        DateTimeImmutable $arrivalDate,
        string $skills,
        array $preferredPosition,
        bool $printedHandbook,
        string $notes,
        DateTimeImmutable $registrationCloseDate,
        DateTimeImmutable $registrationApproveDate,
        ?DateTimeImmutable $registrationPayDate,
        string $variableSymbol,
        int $price,
        PaymentStatus $paymentStatus,
        string $accountNumber,
        string $iban,
        string $swift,
        DateTimeImmutable $due,
    ): User {
        $user = new User();
        $user->email = $email;
        $user->skautisId = $skautisId;
        $user->skautisHasMembership = true;
        $user->role = UserRole::Participant;
        $user->event = $event;
        $user->status = $userStatus;
        $user->loginType = UserLoginType::Skautis;

        $this->userRepository->persist($user);

        $participant = new Participant();
        $participant->user = $user;
        $participant->role = $participantRole;
        $participant->contingent = $contingent;
        $participant->firstName = $firstName;
        $participant->lastName = $lastName;
        $participant->nickname = $nickname;
        $participant->permanentResidence = $permanentResidence;
        $participant->telephoneNumber = $telephoneNumber;
        $participant->email = $email;
        $participant->scoutUnit = $scoutUnit;
        $participant->birthDate = $birthDate;
        $participant->healthProblems = $healthProblems;
        $participant->medicaments = $medicaments;
        $participant->psychicalHealthProblems = $psychicalHealthProblems;
        $participant->foodPreferences = $foodPreferences;
        $participant->arrivalDate = $arrivalDate;
        $participant->skills = $skills;
        $participant->preferredPosition = $preferredPosition;
        $participant->printedHandbook = $printedHandbook;
        $participant->notes = $notes;
        $participant->registrationCloseDate = $registrationCloseDate;
        $participant->registrationApproveDate = $registrationApproveDate;
        $participant->registrationPayDate = $registrationPayDate;

        $this->participantRepository->persist($participant);

        $payment = new Payment();
        $payment->variableSymbol = $variableSymbol;
        $payment->price = (string)$price;
        $payment->currency = 'Kč';
        $payment->status = $paymentStatus;
        $payment->purpose = 'fee';
        $payment->accountNumber = $accountNumber;
        $payment->iban = $iban;
        $payment->swift = $swift;
        $payment->due = $due;
        $payment->note = $event->slug . ' ' . $participant->getFullName();
        $payment->participant = $participant;

        $this->paymentRepository->persist($payment);

        return $user;
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

    public function setUserCancelled(User $user): User
    {
        $user->status = UserStatus::Cancelled;
        $this->userRepository->persist($user);

        return $user;
    }
}
