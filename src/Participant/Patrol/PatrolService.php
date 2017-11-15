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

	public function addParticipant(PatrolLeader $patrolLeader, string $firstName, string $lastName, string $allergies) {
		$participant = new PatrolParticipant();
		$participant->patrolLeader = $patrolLeader;
		$participant->firstName = $firstName;
		$participant->lastName = $lastName;
		$participant->allergies = $allergies;
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
		$patrolLeader->finished = false;
		$this->patrolLeaderRepository->persist($patrolLeader);
	}

	public function addPatrolLeaderInfo(PatrolLeader $patrolLeader,
	                                    string $firstName,
	                                    string $lastName,
	                                    string $allergies,
	                                    string $dateOfBirth,
	                                    string $permanentResidence,
	                                    string $telephoneNumber,
	                                    string $scoutUnit,
	                                    string $country,
	                                    string $notes) {
		$patrolLeader->firstName = $firstName;
		$patrolLeader->lastName = $lastName;
		$patrolLeader->allergies = $allergies;
		$this->patrolLeaderRepository->persist($patrolLeader);
	}
}