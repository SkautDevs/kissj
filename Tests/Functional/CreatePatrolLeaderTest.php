<?php

namespace Tests\Functional;


use kissj\Participants\Patrol\PatrolService;
use kissj\User\UserService;


class CreatePatrolLeaderTest extends BaseTestCase {

    /**
     * Test that the index route returns a rendered response containing the text 'SlimFramework' but not a greeting
     */
    public function testRegisterAndLogin() {
        $app = $this->app();
        /** @var UserService $userService */
        $userService = $app->getContainer()->get('userService');
        /** @var PatrolService $patrolService */
        $patrolService = $app->getContainer()->get('patrolService');

        $email = 'test2@example.com';
        $user = $userService->registerUser($email);
        $patrolLeader = $patrolService->getPatrolLeader($user);

        $this->assertEquals($patrolLeader->user->id, $user->id);
    }
}
