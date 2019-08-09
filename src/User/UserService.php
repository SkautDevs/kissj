<?php

namespace kissj\User;

use kissj\Mailer\MailerInterface;
use kissj\Random;
use Slim\Router;
use Slim\Views\Twig;

class UserService {

    private $router;
    private $random;
    private $eventName;
    private $renderer;

    /** @var UserRepository */
    private $userRepository;
    /** @var MailerInterface */
    private $mailer;
    /** @var LoginTokenRepository */
    private $loginTokenRepository;

    public function __construct(
        UserRepository $userRepository,
        LoginTokenRepository $loginTokenRepository,
        MailerInterface $mailer,
        Router $router,
        Random $random,
        string $eventName,
        Twig $renderer
    ) {
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
        $this->loginTokenRepository = $loginTokenRepository;
        $this->router = $router;
        $this->random = $random;
        $this->eventName = $eventName;
        $this->renderer = $renderer;
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

    public function sendLoginTokenByMail(
        string $email,
        ?string $readableRole = null,
        ?string $eventReadableNameLong = null
    ): string {
        $user = $this->userRepository->findOneBy(['email' => $email]);
        $this->invalidateAllLoginTokens($user);

        // generate new token
        $loginToken = new LoginToken();
        $token = $this->random->generateToken();
        $loginToken->token = $token;
        $loginToken->user = $user;
        $loginToken->created = new \DateTime();
        $loginToken->used = false;

        $this->loginTokenRepository->persist($loginToken);

        $link = $this->router->pathFor('loginWithToken', ['token' => $token]);
        $message = $this->renderer->fetch('emails/login-token.twig',
            ['link' => $link, 'eventName' => $eventReadableNameLong, 'readableRole' => $readableRole]);
        $this->mailer->sendMail($email, 'Link s přihlášením', $message);

        return $token;
    }

    public function isLoginTokenValid(string $loginToken): bool {
        try {
            $lastToken = $this->loginTokenRepository->findOneBy(['token' => $loginToken, 'used' => false],
                ['created' => false]);
            if (is_null($lastToken)) {
                return false;
            }

            $lastValidTime = new \DateTime();
            $lastValidTime->modify("-15 minutes");
            if ($lastToken->created < $lastValidTime) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
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

    public function logoutUser() {
        unset($_SESSION['user']);
    }

    public function invalidateAllLoginTokens($user) {
        // invalidate all not yet used login tokens
        $existingTokens = $this->loginTokenRepository->findBy([$user, 'used' => false]);
        foreach ($existingTokens as $token) {
            $token->used = true;
            $this->loginTokenRepository->persist($token);
        }
    }

    public function getReadableRoleName(string $role): string {
        switch ($role) {
            case 'patrol-leader':
                return 'Patrol Leader';
            case 'ist':
                return 'International Service Team';
            case 'admin':
                return 'administrator';
            default:
                throw new \Exception('Unknown role name');
        }
    }

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
