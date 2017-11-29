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
	private $possibleRoles;
	
	/** @var UserRepository */
	private $userRepository;
	/** @var RoleRepository */
	private $roleRepository;
	/** @var MailerInterface */
	private $mailer;
	/** @var LoginTokenRepository */
	private $loginTokenRepository;
	
	private $statusService;
	
	public function __construct(UserRepository $userRepository,
								RoleRepository $roleRepository,
								LoginTokenRepository $loginTokenRepository,
								MailerInterface $mailer,
								Router $router,
								Random $random,
								string $eventName,
								$renderer,
								array $possibleRoles) {
		$this->userRepository = $userRepository;
		$this->mailer = $mailer;
		$this->loginTokenRepository = $loginTokenRepository;
		$this->router = $router;
		$this->random = $random;
		$this->eventName = $eventName;
		$this->renderer = $renderer;
		$this->possibleRoles = $possibleRoles;
		$this->roleRepository = $roleRepository;
		
		$this->statusService = new UserStatusService();
	}
	
	public function isUserRoleNameValid(string $role): bool {
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
	
	public function getRole(User $user, string $event = 'cej2018'): Role {
		return $this->roleRepository->findOneBy(['user' => $user]);
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
	
	public function addRole(User $user, string $roleName) {
		$role = new Role();
		$role->name = $roleName;
		$role->user = $user;
		$role->event = $this->eventName;
		$role->status = $this->statusService->getFirstStatus($roleName);
		$this->roleRepository->persist($role);
	}
}