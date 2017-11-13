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
	
	public function registerUser(string $email): User {
		$user = new User();
		$user->email = $email;
		$this->userRepository->persist($user);
		return $user;
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
		// TODO: Implement isLoginTokenValid() method.
	}
	
	public function getUserFromToken(string $token): User {
		$user = $this->loginTokenRepository->findOneBy(['token' => $token])->user;
		return $user;
	}
	
	public function saveUserIntoSession(User $user) {
		// TODO: Implement saveUserIntoSession() method.
		$_SESSION['user']['id'] = $id;
		$_SESSION['user']['email'] = $email;
		$_SESSION['user']['role'] = $role;
	}
	
	public function canRecreateUserFromSession($possibleUserSession): bool {
		return (isset($userSessionUser['id'], $userSessionUser['email'], $userSessionUser['role']));
	}
	
	public function createUserFromSession(array $sessionUser): User {
		// TODO: Implement createUserFromSession() method.
		$id = $sessionUser['id'];
		$email = $sessionUser['email'];
		$role = $sessionUser['role'];
		
		return $user;
	}
	
	public function logoutUser() {
		unset($_SESSION['user']);
	}
}