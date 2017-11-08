<?php

namespace kissj\User;

interface UserServiceInterface {
	public function registerUser(string $email): int;
	
	public function sendLoginLink(string $email): string;
	
	public function getUser(string $token): User;
}