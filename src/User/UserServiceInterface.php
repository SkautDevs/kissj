<?php

namespace kissj\User;

interface UserServiceInterface {
	public function registerUser(string $email): User;
	
	public function sendLoginLink(string $email): bool;
	
	public function isLoginValid(string $token): bool;
	
	public function getUser(string $token): User;
}