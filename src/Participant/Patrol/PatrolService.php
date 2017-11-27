<?php

namespace kissj\Participant\Patrol;

use kissj\User\User;

class PatrolService implements PatrolServiceInterface {
	/** @var PatrolParticipantRepository */
	private $patrolParticipantRepository;
	/** @var PatrolLeaderRepository */
	private $patrolLeaderRepository;
	
	public function __construct(PatrolParticipantRepository $patrolParticipantRepository, PatrolLeaderRepository $patrolLeaderRepository) {
		$this->patrolParticipantRepository = $patrolParticipantRepository;
		$this->patrolLeaderRepository = $patrolLeaderRepository;
	}
	
	public function getPatrolLeader(User $user): PatrolLeader {
		if ($this->patrolLeaderRepository->countBy(['user' => $user]) === 0) {
			$patrolLeader = new PatrolLeader();
			$patrolLeader->user = $user;
			$patrolLeader->finished = false;
			$this->patrolLeaderRepository->persist($patrolLeader);
			return $patrolLeader;
		}
		
		$patrolLeader = $this->patrolLeaderRepository->findOneBy(['user' => $user]);
		return $patrolLeader;
	}
	
	public function isPatrolLeaderDetailsValid(string $firstName,
											   string $lastName,
											   string $allergies,
											   string $birthDate,
											   string $birthPlace,
											   string $country,
											   string $gender,
											   string $permanentResidence,
											   string $scoutUnit,
											   string $telephoneNumber,
											   string $email,
											   string $foodPreferences,
											   string $cardPassportNumber,
											   string $notes,
											   string $patrolName): bool {
		$validFlag = true;
		
		if (!empty($birthDate) && $birthDate !== date('Y-m-d', strtotime($birthDate))) {
			$validFlag = false;
		}
		// check for numbers and plus sight up front only
		if ((!empty ($telephoneNumber)) && preg_match('/^\+?\d+$/', $telephoneNumber) === 0) {
			$validFlag = false;
		}
		if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
			$validFlag = false;
		}
		
		return $validFlag;
	}
	
	public function editPatrolLeaderInfo(PatrolLeader $patrolLeader,
										 string $firstName,
										 string $lastName,
										 string $allergies,
										 string $birthDate,
										 string $birthPlace,
										 string $country,
										 string $gender,
										 string $permanentResidence,
										 string $scoutUnit,
										 string $telephoneNumber,
										 string $email,
										 string $foodPreferences,
										 string $cardPassportNumber,
										 string $notes,
										 string $patrolName) {
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
	
	public function isPatrolParticipantDetailsValid(?string $firstName,
													?string $lastName,
													?string $allergies,
													?\DateTime $birthDate,
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
		
		if (!empty($birthDate) && $birthDate !== date('Y-m-d', strtotime($birthDate))) {
			$validFlag = false;
		}
		// check for numbers and plus sight up front only
		if ((!empty ($telephoneNumber)) && preg_match('/^\+?\d+$/', $telephoneNumber) === 0) {
			$validFlag = false;
		}
		if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
			$validFlag = false;
		}
		
		return $validFlag;
	}
	
	public function editPatrolParticipant(PatrolParticipant $patrolParticipant,
										  ?string $firstName,
										  ?string $lastName,
										  ?string $allergies,
										  ?\DateTime $birthDate,
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
		$patrolParticipant->birthDate = $birthDate;
		$patrolParticipant->birthPlace = new \DateTime($birthDate);
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
	
	public function deletePatrolParticipant(PatrolParticipant $patrolParticipant) {
		$this->patrolParticipantRepository->delete($patrolParticipant);
	}
	
	public function participantBelongsPatrolLeader(PatrolParticipant $patrolParticipant,
												   PatrolLeader $patrolLeader): bool {
		return $patrolParticipant->patrolLeader->id === $patrolLeader->id;
	}
	
	public function closeRegistration(PatrolLeader $patrolLeader) {
		$patrolLeader->finished = true;
		$this->patrolLeaderRepository->persist($patrolLeader);
	}
}