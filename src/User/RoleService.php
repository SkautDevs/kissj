<?php

namespace kissj\User;


class RoleService {
	
	/** @var RoleRepository */
	private $roleRepository;
	
	private $possibleRoles;
	private $eventName;
	private $statuses;
	
	public function __construct(array $possibleRoles,
								RoleRepository $roleRepository,
								string $eventName) {
		$this->possibleRoles = $possibleRoles;
		$this->roleRepository = $roleRepository;
		$this->eventName = $eventName;
		$this->statuses = [
			'open',
			'closed',
			'aprooved',
			'paid'];
	}
	
	// ROLES
	
	public function isUserRoleNameValid(string $role): bool {
		return in_array($role, $this->possibleRoles);
	}
	
	public function getRole(?User $user, string $event = 'cej2018'): ?Role {
		if (is_null($user)) {
			return null;
		} else {
			return $this->roleRepository->findOneBy(['user' => $user]);
		}
	}
	
	public function addRole(User $user, string $roleName) {
		$role = new Role();
		$role->name = $roleName;
		$role->user = $user;
		$role->event = $this->eventName;
		$role->status = $this->getFirstStatus($roleName);
		$this->roleRepository->persist($role);
	}
	
	// STATUSES
	
	// for rendering
	
	public function getHelpForRole(?Role $role): ?string {
		if (is_null($role)) {
			return null;
		} else {
			switch ($role->status) {
				case 'open':
					return 'Vyplň všechny údaje o sobě a potom klikni na Uzavřít registraci dole';
				case 'closed':
					return 'Tvoje registrace čeká na schválení (schvalovat začneme od 1.1.2018). Pokud to trvá moc dlouho, ozvi se nám na mail cej2018@skaut.cz';
				case 'aprooved':
					return 'Tvoje registrace byla přijata! Teď nadchází placení. Tvoje platební údaje jsou níže';
				case 'paid':
					return 'Registraci máš vyplněnou, odevzdanou, přijatou i zaplacenou. Těšíme se na tebe na akci!';
				default:
					throw new \Exception('Unknown role '.$role->status);
			}
		}
	}
	
	// for User class
	
	public function getFirstStatus($role): string {
		// TODO enhance for different roles
		return $this->statuses[0];
	}
	
	/**
	 * @param $role
	 * @return int
	 * @throws \Exception
	 */
	public function getNextStatus($role): string {
		if (!$this->isStatusValid($role)) {
			throw new \Exception('Unknown status "'.$role.'"');
		}
		if ($this->isStatusLast($role)) {
			throw new \Exception('Last role in row');
		}
		
		$key = array_search($role, $this->statuses);
		
		return $this->statuses[$key + 1];
	}
	
	public function isStatusValid($status): bool {
		return in_array($status, $this->statuses);
	}
	
	public function isStatusLast($role): bool {
		return $role == end($this->statuses);
	}
}