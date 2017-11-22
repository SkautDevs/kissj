<?php

namespace Tests\Functional;


use kissj\Participant\Patrol\PatrolService;
use kissj\User\UserService;

class PatrolLeaderTest extends BaseTestCase {

	/**
	 * Test that the index route returns a rendered response containing the text 'SlimFramework' but not a greeting
	 */
	public function testCreatePatrolLeader() {
		$app = $this->app();
		/** @var UserService $userService */
		$userService = $app->getContainer()->get('userService');
		/** @var PatrolService $patrolService */
		$patrolService = $app->getContainer()->get('patrolService');

		$email = 'test2@example.com';
		$user = $userService->registerUser($email);
		$patrolLeader = $patrolService->getPatrolLeader($user);

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
		/** @var PatrolService $patrolService */
		$patrolService = $app->getContainer()->get('patrolService');

		$email = 'test3@example.com';
		$user = $userService->registerUser($email);
		$patrolLeader = $patrolService->getPatrolLeader($user);

		$this->assertEquals($patrolLeader->user->id, $user->id);
		$this->assertFalse($patrolLeader->finished);

		$patrolService->addPatrolLeaderInfo($patrolLeader, 'leader', 'leaderový', 'burákové máslo');

		$this->assertFalse($patrolLeader->finished);
		$patrolService->closeRegistration($patrolLeader);
		$this->assertTrue($patrolLeader->finished);
	}
}