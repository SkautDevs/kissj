<?php

namespace kissj\Participant;

use kissj\User\PatrolLeader;
use kissj\User\User;


/**
 * Created by PhpStorm.
 * User: Azathoth
 * Date: 2017-10-25
 * Time: 20:22
 */
interface ParticipantServiceInterface {
	public function register(PatrolLeader $patrolLeader, string $firstName, string $lastName, string $allergies);

	public function getPatrolLeader(User $user): PatrolLeader;
}