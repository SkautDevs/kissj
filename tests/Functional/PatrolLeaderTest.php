<?php

namespace Tests\Functional;


use kissj\Patrol\ParticipantService;
use kissj\User\UserService;

class PatrolLeaderTest extends BaseTestCase {

	/**
	 * Test that the index route returns a rendered response containing the text 'SlimFramework' but not a greeting
	 */
	public function testCreatePatrolLeader() {
		$app = $this->app();
		/** @var UserService $userService */
		$userService = $app->getContainer()->get('userService');
		/** @var ParticipantService $participantService */
		$participantService = $app->getContainer()->get('participantService');

		$email = 'test2@example.com';
		$user = $userService->registerUser($email);
		$patrolLeader = $participantService->getPatrolLeader($user);

		$this->assertEquals($patrolLeader->user->id, $user->id);
		$this->assertFalse($patrolLeader->finished);
	}

	/**
	 * Test that the index route returns a rendered response containing the text 'SlimFramework' but not a greeting
	 */
	public function testFillRegistration() {
		$app = $this->app();
		/** @var UserService $userService */
		$userService = $app->getContainer()->get('userService');
		/** @var ParticipantService $participantService */
		$participantService = $app->getContainer()->get('participantService');

		$email = 'test3@example.com';
		$user = $userService->registerUser($email);
		$patrolLeader = $participantService->getPatrolLeader($user);

		$this->assertEquals($patrolLeader->user->id, $user->id);
		$this->assertFalse($patrolLeader->finished);

		$participantService->addPatrolLeaderInfo($patrolLeader, 'leader', 'leaderový', 'burákové máslo');

		$this->assertFalse($patrolLeader->finished);
		$participantService->closeRegistration($patrolLeader);
		$this->assertTrue($patrolLeader->finished);
	}
}