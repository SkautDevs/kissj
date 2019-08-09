<?php

namespace Tests\Functional;


use kissj\Participants\Patrol\PatrolService;
use kissj\User\RoleService;
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
        /** @var RoleService $roleService */
        $roleService = $app->getContainer()->get('roleService');

        $email = 'test4@example.com';
        $user = $userService->registerUser($email);
        $patrolLeader = $patrolService->getPatrolLeader($user);
        $role = $roleService->getRole($user);

        $this->assertEquals($patrolLeader->user->id, $user->id);
        $this->assertNotEquals('closed', $role->status);
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
        /** @var RoleService $roleService */
        $roleService = $app->getContainer()->get('roleService');

        $email = 'test3@example.com';
        $user = $userService->registerUser($email);
        $patrolLeader = $patrolService->getPatrolLeader($user);
        $role = $roleService->getRole($user);

        $this->assertEquals($patrolLeader->user->id, $user->id);
        $this->assertEquals('open', $role->status);

        $patrolService->editPatrolLeaderInfo($patrolLeader,
            'leader',
            'leaderový',
            'burákové máslo',
            (new \DateTime())->format(DATE_ISO8601),
            'Kalimdor',
            'Azeroth',
            'attack helicopter',
            'Northrend',
            'High Elves',
            'none',
            'test@test.moe',
            'trolls',
            'some',
            'some note',
            'my great patrol'
        );

        $role = $roleService->getRole($user);
        $this->assertEquals('open', $role->status);
        $patrolService->closeRegistration($patrolLeader);
        $role = $roleService->getRole($user);
        $this->assertEquals('closed', $role->status);
    }

    /**
     * Test that the index route returns a rendered response containing the text 'SlimFramework' but not a greeting
     */
    public function testAddPatrolParticipant() {
        $app = $this->app();
        /** @var UserService $userService */
        $userService = $app->getContainer()->get('userService');
        /** @var PatrolService $patrolService */
        $patrolService = $app->getContainer()->get('patrolService');

        $email = 'test5@example.com';
        $user = $userService->registerUser($email);
        $patrolLeader = $patrolService->getPatrolLeader($user);

        $participant = $patrolService->addPatrolParticipant($patrolLeader);
        $this->assertEquals($patrolLeader->id, $participant->patrolLeader->id);
    }


}
