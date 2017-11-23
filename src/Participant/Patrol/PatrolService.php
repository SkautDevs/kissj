<?php

namespace kissj\Participant\Patrol;

use kissj\User\User;

class PatrolService implements PatrolServiceInterface {
	/** @var PatrolParticipantRepository */
	private $participantRepository;
	/** @var PatrolLeaderRepository */
	private $patrolLeaderRepository;
	
	public function __construct(PatrolParticipantRepository $participantRepository, PatrolLeaderRepository $patrolLeaderRepository) {
		$this->participantRepository = $participantRepository;
		$this->patrolLeaderRepository = $patrolLeaderRepository;
	}
	
	public function addParticipant(PatrolLeader $patrolLeader,
								   string $firstName,
								   string $lastName,
								   string $allergies,
								   \DateTime $birthDate,
								   string $birthPlace,
								   string $country,
								   string $gender,
								   string $permanentResidence,
								   string $scoutUnit,
								   string $telephoneNumber,
								   string $email,
								   string $foodPreferences,
								   string $cardPassportNumber,
								   string $notes) {
		$participant = new PatrolParticipant();
		
		$participant->patrolLeader = $patrolLeader;
		$participant->firstName = $firstName;
		$participant->lastName = $lastName;
		$participant->allergies = $allergies;
		$participant->birthDate = $birthDate;
		$participant->birthPlace = $birthPlace;
		$participant->country = $country;
		$participant->gender = $gender;
		$participant->permanentResidence = $permanentResidence;
		$participant->scoutUnit = $scoutUnit;
		$participant->telephoneNumber = $telephoneNumber;
		$participant->email = $email;
		$participant->foodPreferences = $foodPreferences;
		$participant->cardPassportNumber = $cardPassportNumber;
		$participant->notes = $notes;
		
		$this->participantRepository->persist($participant);
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
	
	public function closeRegistration(PatrolLeader $patrolLeader) {
		$patrolLeader->finished = true;
		$this->patrolLeaderRepository->persist($patrolLeader);
	}
	
	public function addPatrolLeaderInfo(PatrolLeader $patrolLeader,
										string $firstName,
										string $lastName,
										string $allergies,
										\DateTime $birthDate,
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
		$patrolLeader->birthDate = $birthDate;
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
}