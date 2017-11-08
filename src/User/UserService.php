<?php

namespace kissj\User;

use DateTime;
use PDO;
use PHPMailer\PHPMailer\PHPMailer;
use Src\Random;

/**
 * Created by PhpStorm.
 * User: Azathoth
 * Date: 2017-10-25
 * Time: 20:22
 */
class UserService {

	/** @var UserRepository */
	private $userRepository;

	/** @var PHPMailer */
	private $mailer;
	/** @var LoginTokenRepository */
	private $loginTokenRepository;

	public function __construct(UserRepository $userRepository, LoginTokenRepository $loginTokenRepository, PHPMailer $mailer) {
		$this->userRepository = $userRepository;
		$this->mailer = $mailer;
		$this->loginTokenRepository = $loginTokenRepository;
	}

	public function registerUser(string $firstName, string $lastName, string $email, DateTime $birthDate, string $phone, string $country, string $group): int {
		$user = new User();
		$user->firstName = $firstName;
		$user->lastName = $lastName;
		$user->email = $email;
		$user->birthDate = $birthDate;
		$user->country = $country;
		$user->phone = $phone;
		$user->group = $group;
		$this->userRepository->persist($user);
		return $user->id;
	}

	public function sendLoginLink(string $email): string {
		$user = $this->userRepository->findOneBy(['email' => $email]);
		$loginToken = new LoginToken();
		$token = Random::generateToken();
		$loginToken->token = $token;
		$loginToken->user = $user;
		$loginToken->created = new \DateTime();
		$loginToken->used = false;
		$this->loginTokenRepository->persist($loginToken);
//		todo: poslat email s linkem
		return $token;
	}

	public function getUser(string $token): User {
		$user = $this->loginTokenRepository->findOneBy(['token' => $token]);
		return $user;
	}

}