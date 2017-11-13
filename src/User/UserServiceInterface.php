<?php

namespace kissj\User;

interface UserServiceInterface {
	public function registerUser(string $email): User;
	
	public function sendLoginToken(string $email);
	
	public function isLoginTokenValid(string $token): bool;
	
	public function getUserFromToken(string $token): User;
	
	public function saveUserIntoSession(User $user);
	
	public function canRecreateUserFromSession(array $session): bool;
	
	public function createUserFromSession(array $session): User;
	
	public function logoutUser();
}