<?php

namespace kissj\User;

use kissj\Random;
use kissj\Mailer\MailerInterface;
use Slim\Router;

class UserService implements UserServiceInterface {
	
	private $router;
	private $random;
	private $eventName;
	private $renderer;
	private $possibleRoles;
	
	/** @var UserRepository */
	private $userRepository;
	/** @var MailerInterface */
	private $mailer;
	/** @var LoginTokenRepository */
	private $loginTokenRepository;
	
	public function __construct(UserRepository $userRepository, LoginTokenRepository $loginTokenRepository, MailerInterface $mailer, Router $router, Random $random, string $eventName, $renderer, array $possibleRoles) {
		$this->userRepository = $userRepository;
		$this->mailer = $mailer;
		$this->loginTokenRepository = $loginTokenRepository;
		$this->router = $router;
		$this->random = $random;
		$this->eventName = $eventName;
		$this->renderer = $renderer;
		$this->possibleRoles = $possibleRoles;
	}
	
	public function isUserRoleValid(string $role): bool {
		return in_array($role, $this->possibleRoles);
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
		$loginToken = new LoginToken();
		$token = $this->random->generateToken();
		$loginToken->token = $token;
		$loginToken->user = $user;
		$loginToken->created = new \DateTime();
		$loginToken->used = false;
		$this->loginTokenRepository->persist($loginToken);
		
		$link = $this->router->pathFor('loginWithToken', ['token' => $token]);
		$message = $this->renderer->fetch('emails/login-token.twig', ['link' => $link, 'eventName' => $this->eventName]);
		$this->mailer->sendMail($email, 'Link s přihlášením', $message);
		// return token for testing
		return $token;
	}
	
	public function isLoginTokenValid(string $loginToken): bool {
		return !is_null($this->loginTokenRepository->findOneBy(['token' => $loginToken]));
	}
	
	public function getUserFromToken(string $token): User {
		return $this->loginTokenRepository->findOneBy(['token' => $token])->user;
	}

    public function getTokenForEmail(string $email): string {
        $user = $this->userRepository->findOneBy(['email' => $email]);
        return $this->getTokenForUser($user);
    }

    public function getTokenForUser(User $user): string {
        return $this->loginTokenRepository->findOneBy(['user' => $user])->token;
    }
	
	public function getRole(?User $user): string {
		if (is_null($user)) {
			return 'non-logged';
		}
		//$this->userRepository->
		return 'patrol-leader';
	}
	
	public function saveUserIdIntoSession(User $user) {
		$_SESSION['user']['id'] = $user->id;
	}
	
	public function canRecreateUserFromSession($possibleUserSession): bool {
		return $this->userRepository->isExisting(['id' => $possibleUserSession['id'] ?? null]);
	}
	
	public function createUserFromSession(array $sessionUser): User {
		return $this->userRepository->findOneBy(['id' => $sessionUser['id']]);
	}
	
	public function logoutUser() {
		unset($_SESSION['user']);
	}
}