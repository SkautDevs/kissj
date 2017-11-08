<?php

namespace kissj\Participant;

use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\PatrolLeader;
use kissj\User\User;
use PDO;

/**
 * Created by PhpStorm.
 * User: Azathoth
 * Date: 2017-10-25
 * Time: 20:22
 */
class ParticipantService implements ParticipantServiceInterface {

	/**
	 * @var ParticipantRepository
	 */
	private $participantRepository;

	public function __construct(ParticipantRepository $participantRepository) {
		$this->participantRepository = $participantRepository;
	}

	public function register(PatrolLeader $patrolLeader, string $firstName, string $lastName, string $allergies) {
		$participant = new Participant();
		$participant->patrolLeader = $patrolLeader;
		$participant->firstName = $firstName;
		$participant->lastName = $lastName;
		$participant->allergies = $allergies;
		$this->participantRepository->persist($participant);
	}

	public function getPatrolLeader(User $user): PatrolLeader {
		
	}
}