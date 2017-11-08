<?php

namespace kissj\Patrol;

use kissj\User\User;


interface ParticipantServiceInterface {
	public function addPatrolLeaderInfo(PatrolLeader $patrolLeader, string $firstName, string $lastName, string $allergies);

	public function addParticipant(PatrolLeader $patrolLeader, string $firstName, string $lastName, string $allergies);

	public function getPatrolLeader(User $user): PatrolLeader;

	public function closeRegistration(PatrolLeader $patrolLeader);
}