<?php

namespace kissj\User;

use kissj\Random;
use kissj\Mailer\MailerInterface;
use Slim\Router;

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
	
	public function __construct(UserRepository $userRepository,
								LoginTokenRepository $loginTokenRepository,
								MailerInterface $mailer,
								Router $router,
								Random $random,
								string $eventName,
								$renderer) {
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
		// TODO invalidate all other tokens for this User
		
		return $token;
	}
	
	public function isLoginTokenValid(string $loginToken): bool {
		// TODO implement time gate (15 mins from settings preferably)
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
	
	public function logoutUser() {
		unset($_SESSION['user']);
	}
}