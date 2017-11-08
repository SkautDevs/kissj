<?php

namespace kissj\Patrol;

use kissj\User\User;

class ParticipantService implements ParticipantServiceInterface {
	/** @var ParticipantRepository */
	private $participantRepository;
	/** @var PatrolLeaderRepository */
	private $patrolLeaderRepository;

	public function __construct(ParticipantRepository $participantRepository, PatrolLeaderRepository $patrolLeaderRepository) {
		$this->participantRepository = $participantRepository;
		$this->patrolLeaderRepository = $patrolLeaderRepository;
	}

	public function addParticipant(PatrolLeader $patrolLeader, string $firstName, string $lastName, string $allergies) {
		$participant = new Participant();
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

	public function addPatrolLeaderInfo(PatrolLeader $patrolLeader, string $firstName, string $lastName, string $allergies) {
		// TODO: Implement addPatrolLeaderInfo() method.
	}

	public function closeRegistration(PatrolLeader $patrolLeader) {
		// TODO: Implement closeRegistration() method.
	}
}