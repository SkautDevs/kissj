<?php

namespace Tests\Functional;

use kissj\UserService;

class RegisterTest extends BaseTestCase {

	/**
	 * Test that the index route returns a rendered response containing the text 'SlimFramework' but not a greeting
	 */
	public function testRegisterAndLogin() {
		$app = $this->app();
		/** @var UserService $userService */
		$userService = $app->getContainer()->get('userService');

		$email = 'test@example.com';
		$userId = $userService->registerUser('tester', 'tester', $email, new \DateTime(), '+420777777777', 'CZ', 'my group');
		$token = $userService->sendLoginLink($email);
		$loadedId = $userService->getUserId($token);

		$this->assertEquals($userId, $loadedId);
	}
}