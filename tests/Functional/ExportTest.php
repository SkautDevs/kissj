<?php

namespace Tests\Functional;


use kissj\Export\ExportService;
use kissj\User\UserService;

class ExportTest extends BaseTestCase {

	/**
	 * Test that the index route returns a rendered response containing the text 'SlimFramework' but not a greeting
	 */
	public function testExportMedicalData() {
		$app = $this->app();
		/** @var ExportService $exportService */
		$exportService = $app->getContainer()->get('exportService');

//		$email = 'test@example.com';
//		$user = $userService->registerUser($email);
//		$readableRole = 'Patrol Leader';
//		$token = $userService->sendLoginTokenByMail($email, $readableRole);
//		$loadedUser = $userService->getUserFromToken($token);

		$this->assertTrue(false);
	}
}