<?php

namespace kissj\User;

use kissj\Event\Event;
use kissj\Payment\PaymentRepository;

class RoleService {
	
	/** @var RoleRepository */
	private $roleRepository;
	/** @var PaymentRepository */
	private $paymentRepository;
	
	private $possibleRoles;
	private $statuses;
	
	public function __construct(RoleRepository $roleRepository,
								PaymentRepository $paymentRepository) {
		$this->roleRepository = $roleRepository;
		$this->paymentRepository = $paymentRepository;
		$this->possibleRoles = [
			'patrol-leader', // leader of whole patrol with multiple participants under him
			'ist', // self-standing International Service Team
			//'attendee' // self-standing participant with less informations
			//'guest', // one-day visitor
			//'staff', // worker from external services
			//'team', // organizators
			//'contingent-chief', // chief of one contingent only
			'admin' // chief of event - for approving participants & getting useful info about them
		];
		$this->statuses = [
			'open',
			'closed',
			'approved',
			'paid'];
	}
	
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
	
	public function getRole(?User $user, ?Event $event): ?Role {
		if (is_null($user)) {
			return null;
		} else {
			return $this->roleRepository->findOneBy(['user' => $user, 'event' => $event->slug]);
		}
	}
	
	public function addRole(User $user, string $roleName, Event $event) {
		$role = new Role();
		$role->name = $roleName;
		$role->user = $user;
		$role->event = $event->slug;
		$role->status = 'open';
		$this->roleRepository->persist($role);
	}
	
	private function isStatusValid(string $status): bool {
		return in_array($status, $this->statuses);
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
	
	public function getPaidStatus(): string {
		return 'paid';
	}
}