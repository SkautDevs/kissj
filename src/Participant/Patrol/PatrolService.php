<?php

namespace kissj\Participant\Patrol;

use kissj\FlashMessages\FlashMessagesInterface;
use kissj\Orm\Relation;
use kissj\User\Role;
use kissj\User\RoleRepository;
use kissj\User\RoleService;
use kissj\User\User;
use kissj\User\UserStatusService;

class PatrolService {
	/** @var PatrolParticipantRepository */
	private $patrolParticipantRepository;
	/** @var PatrolLeaderRepository */
	private $patrolLeaderRepository;
	/** @var \kissj\User\RoleRepository */
	private $roleRepository;
	/** @var RoleService */
	private $roleService;
	private $eventSettings;
	private $flashMessages;

	private $eventName;

	public function __construct(PatrolParticipantRepository $patrolParticipantRepository,
								PatrolLeaderRepository $patrolLeaderRepository,
								RoleRepository $roleRepository,
								RoleService $roleService,
								FlashMessagesInterface $flashMessages,
								$eventSettings,
								$eventName = 'cej2018') {
		$this->patrolParticipantRepository = $patrolParticipantRepository;
		$this->patrolLeaderRepository = $patrolLeaderRepository;
		$this->roleRepository = $roleRepository;
		$this->roleService = $roleService;
		$this->flashMessages = $flashMessages;
		$this->eventSettings = $eventSettings;
		$this->eventName = $eventName;
	}
	
	public function getPatrolLeader(User $user): PatrolLeader {
		if ($this->patrolLeaderRepository->countBy(['user' => $user]) === 0) {
			$patrolLeader = new PatrolLeader();
			$patrolLeader->user = $user;
			$this->patrolLeaderRepository->persist($patrolLeader);

			$this->roleService->addRole($user, 'patrol-leader');
		}

		$patrolLeader = $this->patrolLeaderRepository->findOneBy(['user' => $user]);
		return $patrolLeader;
	}
	
	public function getPatrolLeaderFromId(int $patrolLeaderId): PatrolLeader {
		return $this->patrolLeaderRepository->findOneBy(['id' => $patrolLeaderId]);
	}
	
	public function getAllClosedPatrols(string $eventName = 'cej2018'): array {
		$closedPatrols = $this->roleRepository->findBy([
			'event' => $eventName,
			'name' => 'patrol-leader',
			'status' => 'closed']);
		$patrols = [];
		/** @var Role $closedPatrol */
		foreach ($closedPatrols as $closedPatrol) {
			$patrol['patrolLeader'] = $this->patrolLeaderRepository->findOneBy(['userId' => $closedPatrol->user->id]);
			$patrol['patrolParticipants'] = $this->patrolParticipantRepository->findBy(['patrolLeaderId' => $closedPatrol->id]);
			
			$patrols[] = $patrol;
		}
		
		return $patrols;
	}
	
	
	private function isPatrolLeaderValid(PatrolLeader $patrolLeader): bool {
		return $this->isPatrolLeaderDetailsValid(
			$patrolLeader->firstName,
			$patrolLeader->lastName,
			$patrolLeader->allergies,
			($patrolLeader->birthDate ? $patrolLeader->birthDate->format('Y-m-d') : null),
			$patrolLeader->birthPlace,
			$patrolLeader->country,
			$patrolLeader->gender,
			$patrolLeader->permanentResidence,
			$patrolLeader->scoutUnit,
			$patrolLeader->telephoneNumber,
			$patrolLeader->email,
			$patrolLeader->foodPreferences,
			$patrolLeader->cardPassportNumber,
			$patrolLeader->notes,
			$patrolLeader->patrolName
		);
	}
	
	public function isPatrolLeaderDetailsValid(?string $firstName,
											   ?string $lastName,
											   ?string $allergies,
											   ?string $birthDate,
											   ?string $birthPlace,
											   ?string $country,
											   ?string $gender,
											   ?string $permanentResidence,
											   ?string $scoutUnit,
											   ?string $telephoneNumber,
											   ?string $email,
											   ?string $foodPreferences,
											   ?string $cardPassportNumber,
											   ?string $notes,
											   ?string $patrolName): bool {
		$validFlag = true;
		
		if (is_null($firstName) || is_null($lastName) || is_null($birthDate) || is_null($birthPlace) || is_null($country) || is_null($gender) || is_null($permanentResidence) || is_null($scoutUnit) || is_null($telephoneNumber) || is_null($email) || is_null($patrolName)) {
			$validFlag = false;
		}
		
		if (!empty($birthDate) && $birthDate !== date('Y-m-d', strtotime($birthDate))) {
			$validFlag = false;
		}
		// check for numbers and plus sight up front only
		/* if ((!empty ($telephoneNumber)) && preg_match('/^\+?\d+$/', $telephoneNumber) === 0) {
			$validFlag = false;
		}*/
		if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
			$validFlag = false;
		}
		
		return $validFlag;
	}
	
	public function editPatrolLeaderInfo(PatrolLeader $patrolLeader,
										 ?string $firstName,
										 ?string $lastName,
										 ?string $allergies,
										 ?string $birthDate,
										 ?string $birthPlace,
										 ?string $country,
										 ?string $gender,
										 ?string $permanentResidence,
										 ?string $scoutUnit,
										 ?string $telephoneNumber,
										 ?string $email,
										 ?string $foodPreferences,
										 ?string $cardPassportNumber,
										 ?string $notes,
										 ?string $patrolName) {
		$patrolLeader->firstName = $firstName;
		$patrolLeader->lastName = $lastName;
		$patrolLeader->allergies = $allergies;
		$patrolLeader->birthDate = new \DateTime($birthDate);
		$patrolLeader->birthPlace = $birthPlace;
		$patrolLeader->country = $country;
		$patrolLeader->gender = $gender;
		$patrolLeader->permanentResidence = $permanentResidence;
		$patrolLeader->scoutUnit = $scoutUnit;
		$patrolLeader->telephoneNumber = $telephoneNumber;
		$patrolLeader->email = $email;
		$patrolLeader->foodPreferences = $foodPreferences;
		$patrolLeader->cardPassportNumber = $cardPassportNumber;
		$patrolLeader->notes = $notes;
		$patrolLeader->patrolName = $patrolName;
		
		$this->patrolLeaderRepository->persist($patrolLeader);
	}
	
	public function getAllParticipantsBelongsPatrolLeader(PatrolLeader $patrolLeader): array {
		return $this->patrolParticipantRepository->findBy(['patrolleader' => $patrolLeader]);
	}
	
	public function addPatrolParticipant(PatrolLeader $patrolLeader): PatrolParticipant {
		$patrolParticipant = new PatrolParticipant();
		$patrolParticipant->patrolLeader = $patrolLeader;
		
		$this->patrolParticipantRepository->persist($patrolParticipant);
		
		return $patrolParticipant;
	}
	
	public function getPatrolParticipant(int $patrolParticipantId): PatrolParticipant {
		$patrolParticipant = $this->patrolParticipantRepository->findOneBy(['id' => $patrolParticipantId]);
		
		return $patrolParticipant;
	}
	
	private function isPatrolParticipantValid(PatrolParticipant $participant): bool {
		return $this->isPatrolParticipantDetailsValid(
			$participant->firstName,
			$participant->lastName,
			$participant->allergies,
			($participant->birthDate ? $participant->birthDate->format('Y-m-d') : null),
			$participant->birthPlace,
			$participant->country,
			$participant->gender,
			$participant->permanentResidence,
			$participant->scoutUnit,
			$participant->telephoneNumber,
			$participant->email,
			$participant->foodPreferences,
			$participant->cardPassportNumber,
			$participant->notes
		);
	}
	
	public function isPatrolParticipantDetailsValid(?string $firstName,
													?string $lastName,
													?string $allergies,
													?string $birthDate,
													?string $birthPlace,
													?string $country,
													?string $gender,
													?string $permanentResidence,
													?string $scoutUnit,
													?string $telephoneNumber,
													?string $email,
													?string $foodPreferences,
													?string $cardPassportNumber,
													?string $notes): bool {
		$validFlag = true;
		
		if (is_null($firstName) || is_null($lastName) || is_null($birthDate) || is_null($birthPlace) || is_null($country) || is_null($gender) || is_null($permanentResidence) || is_null($scoutUnit) || is_null($telephoneNumber) || is_null($email)) {
			$validFlag = false;
		}
		
		if (!empty($birthDate) && $birthDate !== date('Y-m-d', strtotime($birthDate))) {
			$validFlag = false;
		}
		// check for numbers and plus sight up front only
		/*if ((!empty ($telephoneNumber)) && preg_match('/^\+?\d+$/', $telephoneNumber) === 0) {
			$validFlag = false;
		}*/
		if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
			$validFlag = false;
		}
		
		return $validFlag;
	}
	
	public function editPatrolParticipant(PatrolParticipant $patrolParticipant,
										  ?string $firstName,
										  ?string $lastName,
										  ?string $allergies,
										  ?string $birthDate,
										  ?string $birthPlace,
										  ?string $country,
										  ?string $gender,
										  ?string $permanentResidence,
										  ?string $scoutUnit,
										  ?string $telephoneNumber,
										  ?string $email,
										  ?string $foodPreferences,
										  ?string $cardPassportNumber,
										  ?string $notes) {
		$patrolParticipant->firstName = $firstName;
		$patrolParticipant->lastName = $lastName;
		$patrolParticipant->allergies = $allergies;
		$patrolParticipant->birthDate = new \DateTime($birthDate);
		$patrolParticipant->birthPlace = $birthPlace;
		$patrolParticipant->country = $country;
		$patrolParticipant->gender = $gender;
		$patrolParticipant->permanentResidence = $permanentResidence;
		$patrolParticipant->scoutUnit = $scoutUnit;
		$patrolParticipant->telephoneNumber = $telephoneNumber;
		$patrolParticipant->email = $email;
		$patrolParticipant->foodPreferences = $foodPreferences;
		$patrolParticipant->cardPassportNumber = $cardPassportNumber;
		$patrolParticipant->notes = $notes;
		
		$this->patrolParticipantRepository->persist($patrolParticipant);
	}
	
	/**
	 * @param PatrolParticipant $patrolParticipant
	 * @throws \LeanMapper\Exception\InvalidStateException
	 */
	public function deletePatrolParticipant(PatrolParticipant $patrolParticipant) {
		$this->patrolParticipantRepository->delete($patrolParticipant);
	}
	
	public function patrolParticipantBelongsPatrolLeader(PatrolParticipant $patrolParticipant,
														 PatrolLeader $patrolLeader): bool {
		return $patrolParticipant->patrolLeader->id === $patrolLeader->id;
	}
	
	public function isCloseRegistrationValid(PatrolLeader $patrolLeader): bool {
		$validityFlag = true;
		if (!$this->isPatrolLeaderValid($patrolLeader)) {
			$this->flashMessages->warning('Údaje Patrol Leadera nejsou kompletní');
			$validityFlag = false;
		}
		$participants = $this->getAllParticipantsBelongsPatrolLeader($patrolLeader);
		$participantsCount = count($participants);
		if ($participantsCount < $this->eventSettings['minimalPatrolParticipantsCount']) {
			$this->flashMessages->warning('Účastníků v patrole je příliš málo - je jich jen '.$participantsCount.' z '.$this->eventSettings['minimalPatrolParticipantsCount']);
			$validityFlag = false;
		}
		if ($participantsCount > $this->eventSettings['maximalPatrolParticipantsCount']) {
			$this->flashMessages->warning('Účastníků je v patrole moc - je jich '.$participantsCount.' místo '.$this->eventSettings['maximalPatrolParticipantsCount']);
			$validityFlag = false;
		}
		foreach ($participants as $participant) {
			if (!$this->isPatrolParticipantValid($participant)) {
				$participantName = $participant->getFirstName().' '.$participant->getLastName();
				$this->flashMessages->warning('Údaje účastníka '.$participantName.' nejsou kompletní');
				$validityFlag = false;
			}
		}
		if ($this->getClosedPatrolsCount() >= $this->eventSettings['maximalClosedPatrolsCount']) {
			$this->flashMessages->warning('Registraci už má uzavřenou maximální počet možných patrol a tvoje patrola se nevejde do počtu. Počkej prosím na zvýšení limitu pro patroly.');
			$validityFlag = false;
		}
		
		return $validityFlag;
	}
	
	private function getClosedPatrolsCount(): int {
		return $this->roleRepository->countBy([
			'name' => 'patrol-leader',
			'event' => $this->eventName,
			'status' => new Relation('open', '!=')
		]);
	}
	
	/**
	 * @param PatrolLeader $patrolLeader
	 * @throws \Exception
	 */
	public function closeRegistration(PatrolLeader $patrolLeader) {
		/** @var Role $role */
		$role = $this->roleRepository->findOneBy(['userId' => $patrolLeader->user->id]);
		$role->status = $this->roleService->getNextStatus($role->status);
		$this->roleRepository->persist($role);
	}
	
	public function approvePatrol(int $patrolLeaderId) {
		/** @var PatrolLeaderRepository $patrol */
		$patrol = $this->patrolLeaderRepository->findOneBy(['id' => $patrolLeaderId]);
		/** @var Role $role */
		$role = $this->roleRepository->findOneBy(['userId' => $patrol->user->id]);
		$role->status = $this->roleService->getNextStatus($role->status);
		$this->roleRepository->persist($role);
	}
	
	// TODO make more concrete - set right status directly
	public function openPatrol(int $patrolLeaderId) {
		/** @var PatrolLeaderRepository $patrol */
		$patrol = $this->patrolLeaderRepository->findOneBy(['id' => $patrolLeaderId]);
		/** @var Role $role */
		$role = $this->roleRepository->findOneBy(['userId' => $patrol->user->id]);
		$role->status = $this->roleService->getPreviousStatus($role->status);
		$this->roleRepository->persist($role);
	}
}