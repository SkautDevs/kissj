<?php

namespace kissj\Participant\Patrol;

use kissj\User\User;


interface PatrolServiceInterface {
	public function addPatrolLeaderInfo(PatrolLeader $patrolLeader,
										string $firstName,
										string $lastName,
										string $allergies,
										string $dateOfBirth,
										string $permanentResidence,
										string $telephoneNumber,
										string $scoutUnit,
										string $country,
										string $notes);
	
	public function addParticipant(PatrolLeader $patrolLeader,
								   string $firstName,
								   string $lastName,
								   string $allergies);
	
	public function getPatrolLeader(User $user): PatrolLeader;
	
	public function closeRegistration(PatrolLeader $patrolLeader);
}