<?php

namespace kissj\User;

interface UserServiceInterface {
	public function registerUser(string $email): User;
	
	public function sendLoginToken(string $email);
	
	public function isLoginTokenValid(string $token): bool;
	
	public function getUserFromToken(string $token): User;
	
	public function saveUserIntoSession(User $user);
	
	public function canRecreateUserFromSession($possibleUserSession): bool; // not input type array, because cull can be given
	
	public function createUserFromSession(array $userSession): User;
	
	public function logoutUser();
}