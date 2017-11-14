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
	
	/** @var UserRepository */
	private $userRepository;
	/** @var MailerInterface */
	private $mailer;
	/** @var LoginTokenRepository */
	private $loginTokenRepository;
	
	public function __construct(UserRepository $userRepository, LoginTokenRepository $loginTokenRepository, MailerInterface $mailer, Router $router, Random $random, string $eventName, $renderer) {
		$this->userRepository = $userRepository;
		$this->mailer = $mailer;
		$this->loginTokenRepository = $loginTokenRepository;
		$this->router = $router;
		$this->random = $random;
		$this->eventName = $eventName;
		$this->renderer = $renderer;
	}
	
	public function registerUser(string $email) {
		$user = new User();
		$user->email = $email;
		$this->userRepository->persist($user);
	}
	
	public function sendLoginTokenByMail(string $email) {
		$user = $this->userRepository->findOneBy(['email' => $email]);
		$loginToken = new LoginToken();
		$token = $this->random->generateToken();
		$loginToken->token = $token;
		$loginToken->user = $user;
		$loginToken->created = new \DateTime();
		$loginToken->used = false;
		$this->loginTokenRepository->persist($loginToken);
		
		// TODO check if this is rightly implemented
		$link = $this->router->pathFor('login', ['token' => $token]);
		$message = $this->renderer->fetch('emails/login-token.twig', ['link' => $link, 'eventName' => $this->eventName]);
		$this->mailer->sendMail($email, 'Link s přihlášením', $message);
	}
	
	public function isLoginTokenValid(string $loginToken): bool {
		return empty($this->loginTokenRepository->findOneBy(['token', $loginToken]));
	}
	
	public function getUserFromToken(string $token): User {
		$user = $this->loginTokenRepository->findOneBy(['token' => $token])->user;
		return $user;
	}
	
	public function saveUserIdIntoSession(User $user) {
		$_SESSION['user']['id'] = $user->id;
	}
	
	public function canRecreateUserFromSession($possibleUserSession): bool {
		return (isset($possibleUserSession['id']));
	}
	
	public function createUserFromSession(array $sessionUser): User {
		return $this->userRepository->findOneBy(['id' => $sessionUser['id']]);
	}
	
	public function logoutUser() {
		unset($_SESSION['user']);
	}
}