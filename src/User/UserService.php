<?php

namespace kissj\User;

use kissj\Mailer\MailerInterface;
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
        MailerInterface $mailer,
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
        $this->mailer->sendMailFromTemplate($email, 'Registrace './*$event->readableName.*/
            '- Link s přihlášením', 'login-token', ['link' => $link]);

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

    public function getUserFromToken(string $token): User {
        return $this->loginTokenRepository->findOneBy(['token' => $token])->user;
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
        //$participant->setUser($user);
        $participant->user = $user;
        $this->participantRepository->persist($participant);

        $user->role = $role;
        $user->status = User::STATUS_OPEN;
        $this->userRepository->persist($user);
    }

    protected function isRoleValid(string $role): bool {
        return in_array($role, [User::ROLE_IST, User::ROLE_PATROL_LEADER, User::ROLE_GUEST], true);
    }

    // TODO move to template
    public function getHelpForRole(
        ?Role $role
    ): ?string {
        if (is_null($role)) {
            return null;
        }
        switch ($role->name) {
            case 'admin':
                {
                    return null;
                }

            default:
                {
                    switch ($role->status) {
                        case 'open':
                            {
                                switch ($role->name) {
                                    case 'patrol-leader':
                                        return 'Vyplň všechny údaje o sobě, přidej správný počet účastníků, vyplň údaje i u nich a potom dole klikni na tlačítko Uzavřít registraci.';
                                    case 'ist':
                                        return 'Vyplň všechny údaje o sobě a potom dole klikni na Uzavřít registraci.';
                                    default:
                                        throw new \Exception('Unknown/unimplemented name of role: '.$role->name);
                                }

                            }
                        case 'closed':
                            return 'Tvoje registrace čeká na schválení. Po schválení ti pošleme email s platebními údaji. Pokud to trvá moc dlouho, ozvi se nám na mail korbo@skaut.cz';
                        case 'approved':
                            return 'Tvoje registrace byla přijata! Teď nadchází placení. Tvoje platební údaje jsou níže.';
                        case 'paid':
                            return 'Registraci máš vyplněnou, odevzdanou, přijatou i zaplacenou. Těšíme se na tebe na akci!';
                        default:
                            throw new \Exception('Unknown role: '.$role->status);
                    }
                }
        }
    }
}
