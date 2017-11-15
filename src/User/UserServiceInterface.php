<?php

namespace kissj\User;

interface UserServiceInterface {
	public function isUserRoleValid(string $role): bool;
	
	public function registerUser(string $email): User;
	
	public function isEmailExisting(string $email): bool;
	
	public function sendLoginTokenByMail(string $email);
	
	public function isLoginTokenValid(string $token): bool;
	
	public function getUserFromToken(string $token): User;
	
	public function getRole(User $user): string;
	
	public function saveUserIdIntoSession(User $user);
	
	public function canRecreateUserFromSession($possibleUserSession): bool; // not input type array, because null can be given
	
	public function createUserFromSession(array $userSession): User;
	
	public function logoutUser();
}