<?php

namespace Src\Participant;

use kissj\User\Participant;
use kissj\User\ParticipantRepository;
use kissj\User\PatrolLeader;
use kissj\User\User;
use PDO;

/**
 * Created by PhpStorm.
 * User: Azathoth
 * Date: 2017-10-25
 * Time: 20:22
 */
class ParticipantService {

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

}