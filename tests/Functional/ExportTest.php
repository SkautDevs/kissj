<?php

namespace Tests\Functional;


use kissj\Export\ExportService;
use kissj\Participant\Patrol\PatrolService;
use kissj\User\RoleService;
use kissj\User\UserService;

class ExportTest extends BaseTestCase {

	/**
	 * Test that the index route returns a rendered response containing the text 'SlimFramework' but not a greeting
	 */
	public function testExportMedicalData() {
		$app = $this->app();
		/** @var ExportService $exportService */
		$exportService = $app->getContainer()->get('exportService');
		/** @var UserService $userService */
		$userService = $app->getContainer()->get('userService');
		/** @var PatrolService $patrolService */
		$patrolService = $app->getContainer()->get('patrolService');
		/** @var RoleService $roleService */
		$roleService = $app->getContainer()->get('roleService');

		for ($i = 0; $i < 10; $i++) {
			$email = 'test-'. $i . '@example.com';
			$user = $userService->registerUser($email);
			$patrolLeader = $patrolService->getPatrolLeader($user);
			$patrolService->editPatrolLeaderInfo($patrolLeader,
				'leader',
				'leaderový',
				'burákové máslo' . $i,
				(new \DateTime())->format(DATE_ISO8601),
				'Kalimdor',
				'Azeroth',
				'attack helicopter',
				'Northrend',
				'High Elves',
				'none',
				'test'.$i.'@test.moe',
				'trolls',
				'some',
				'some note',
				'my great patrol'
			);
		}

		$rows = $exportService->medicalDataToCSV('cej2018');
//		$email = 'test@example.com';
//		$user = $userService->registerUser($email);
//		$readableRole = 'Patrol Leader';
//		$token = $userService->sendLoginTokenByMail($email, $readableRole);
//		$loadedUser = $userService->getUserFromToken($token);


		$this->assertCount(11, $rows);
	}
}