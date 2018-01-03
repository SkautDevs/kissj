<?php

namespace kissj\User;


class RoleService {
	
	/** @var RoleRepository */
	private $roleRepository;
	
	private $possibleRoles;
	private $eventName;
	private $statuses;
	
	public function __construct(RoleRepository $roleRepository,
								string $eventName) {
		$this->roleRepository = $roleRepository;
		$this->eventName = $eventName;
		$this->possibleRoles = [
			'patrol-leader',
			'ist',
			//'guest',
			//'staff',
			//'team',
			//'event-chief',
			//'contingent-chief',
			'admin'
		];
		$this->statuses = [
			'open',
			'closed',
			'approved',
			'paid'];
	}
	
	
	// ROLES
	
	public function getReadableRoleName(string $role): string {
		switch ($role) {
			case 'patrol-leader':
				return 'Patrol Leader';
			case 'ist':
				return 'International Service Team';
			case 'admin':
				return 'administrator';
			default:
				throw new \Exception('Unknown role name');
		}
	}
	
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
		}
		switch ($role->name) {
			case 'admin':
				{
					return null;
				}
			
			default:
				{
					switch ($role->status) {
						case 'open':
							{
								switch ($role->name) {
									case 'patrol-leader':
										return 'Vyplň všechny údaje o sobě, přidej správný počet účastníků, vyplň údaje i u nich a potom dole klikni na tlačítko Uzavřít registraci.';
									case 'ist':
										return 'Vyplň všechny údaje o sobě a potom dole klikni na Uzavřít registraci.';
									default:
										throw new \Exception('Unknown/unimplemented name of role: '.$role->name);
								}
								
							}
						case 'closed':
							return 'Tvoje registrace čeká na schválení (schvalovat začínáme od 1.1.2018). Pokud to trvá moc dlouho, ozvi se nám na mail cej2018@skaut.cz';
						case 'approved':
							return 'Tvoje registrace byla přijata! Teď nadchází placení. Tvoje platební údaje jsou níže.';
						case 'paid':
							return 'Registraci máš vyplněnou, odevzdanou, přijatou i zaplacenou. Těšíme se na tebe na akci!';
						default:
							throw new \Exception('Unknown role: '.$role->status);
					}
				}
		}
	}
	
	
	// for Payment class
	
	public function setPaid(User $user) {
		// TODO implement
		
		// search for corrent Role with $this->eventName;
		$role = $this->getRole($user, $this->eventName);
		// check if Role has status one before approoved with getPreviousStatus
		
		// set paid to Role
	}
	
	
	// for User class
	
	public function getFirstStatus($role): string {
		// TODO enhance for different roles
		return $this->statuses[0];
	}
	
	public function getNextStatus(string $status): string {
		if (!$this->isStatusValid($status)) {
			throw new \Exception('Unknown status "'.$status.'"');
		}
		if ($this->isStatusLast($status)) {
			throw new \Exception('Last role possible');
		}
		
		$key = array_search($status, $this->statuses);
		
		return $this->statuses[$key + 1];
	}
	
	public function getPreviousStatus(string $status): string {
		if (!$this->isStatusValid($status)) {
			throw new \Exception('Unknown status "'.$status.'"');
		}
		if ($this->isStatusFirst($status)) {
			throw new \Exception('First role possible');
		}
		
		$key = array_search($status, $this->statuses);
		
		return $this->statuses[$key - 1];
	}
	
	private function isStatusValid(string $status): bool {
		return in_array($status, $this->statuses);
	}
	
	private function isStatusLast(string $status): bool {
		return $status == end($this->statuses);
	}
	
	private function isStatusFirst(string $status): bool {
		return $status == $this->statuses[0];
	}
	
	public function getOpenStatus(): string {
		return 'open';
	}
	
	public function getCloseStatus(): string {
		return 'closed';
	}
	
	public function getApproveStatus(): string {
		return 'approved';
	}
}