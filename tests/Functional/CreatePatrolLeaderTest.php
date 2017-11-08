<?php

namespace Tests\Functional;


use kissj\Participant\ParticipantService;
use kissj\User\UserService;

class CreatePatrolLeaderTest extends BaseTestCase {

	/**
	 * Test that the index route returns a rendered response containing the text 'SlimFramework' but not a greeting
	 */
	public function testRegisterAndLogin() {
		$app = $this->app();
		/** @var UserService $userService */
		$userService = $app->getContainer()->get('userService');
		/** @var ParticipantService $participantService */
		$participantService = $app->getContainer()->get('participantService');

		$email = 'test2@example.com';
		$user = $userService->registerUser($email);
		$patrolLeader = $participantService->getPatrolLeader($user);

		$this->assertEquals($patrolLeader->user->id, $user->id);
	}
}