<?php

namespace Src;

use DateTime;
use PDO;

/**
 * Created by PhpStorm.
 * User: Azathoth
 * Date: 2017-10-25
 * Time: 20:22
 */
class UserService {

	/** @var PDO */
	private $conn;

	public function __construct(PDO $conn) {
		$this->conn = $conn;
	}

	public function registerUser(string $firstName, string $lastName, string $email, DateTime $birthDate, string $phone, string $country, string $group): int {
		$stmt = $this->conn->prepare("INSERT INTO users (first_name, last_name, email, birth_date, phone, country, \"group\") VALUES (?, ?, ?, ?, ?, ?, ?)");
		$stmt->execute([$firstName, $lastName, $email, $birthDate->format('Y-m-d H:i:s'), $phone, $country, $group]);
		return $this->conn->lastInsertId();
	}

	public function sendLoginLink(string $email): string {
		$userId = $this->conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1")->execute([$email]);
		$stmt = $this->conn->prepare("INSERT INTO login_tokens (token, user_id, created, used) VALUES (?, ?, ?, ?)");
		$token = Random::generateToken();
		$stmt->execute([$token, $userId, new \DateTime(), false]);
//		todo: poslat email s linkem
		return $token;
	}

	public function getUserId(string $token): int {
		$stmt = $this->conn->prepare("SELECT user_id FROM login_tokens WHERE token = ? LIMIT 1");
		$user_id = $stmt->execute([$token]);
		return $user_id;
	}

}