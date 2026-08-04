<?php

declare(strict_types=1);

namespace Tests\Unit\Participant\Admin;

use kissj\Event\EventRepository;
use kissj\Participant\Admin\AdminService;
use kissj\Participant\ParticipantRole;
use kissj\User\User;
use kissj\User\UserLoginType;
use kissj\User\UserRepository;
use kissj\User\UserRole;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Tests\AppTestCase;

class ParticipantSectionsTest extends AppTestCase
{
    public function testSectionsSkipDisabledRolesAndKeepOrder(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $this->getTestSlugEvent($eventRepository);

        $this->mutateEventForTest($container, $event, [
            'allowIsts' => true,
            'allowPatrols' => false,
            'allowGuests' => true,
        ]);

        /** @var AdminService $adminService */
        $adminService = $container->get(AdminService::class);

        $sections = $adminService->getParticipantSections(
            $event,
            $this->createEventAdminUser($container),
            UserStatus::Open,
            ['ist' => null, 'pl' => null, 'guest' => 'test.empty-key'],
        );

        self::assertCount(2, $sections);
        self::assertSame(ParticipantRole::Ist, $sections[0]->role);
        self::assertSame('role.ist', $sections[0]->titleKey);
        self::assertNull($sections[0]->emptyTitleKey);
        self::assertSame(ParticipantRole::Guest, $sections[1]->role);
        self::assertSame('test.empty-key', $sections[1]->emptyTitleKey);
    }

    public function testGroupLeaderSectionsCarryChildArbiter(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $this->getTestSlugEvent($eventRepository);

        $this->mutateEventForTest($container, $event, ['allowPatrols' => true, 'allowIsts' => true]);

        /** @var AdminService $adminService */
        $adminService = $container->get(AdminService::class);

        $sections = $adminService->getParticipantSections(
            $event,
            $this->createEventAdminUser($container),
            UserStatus::Open,
            ['pl' => null, 'ist' => null],
        );

        self::assertNotNull($sections[0]->childContentArbiter);
        self::assertNull($sections[1]->childContentArbiter);
    }

    private function createEventAdminUser(ContainerInterface $container): User
    {
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);

        $user = new User();
        $user->event = $this->getTestSlugEvent($eventRepository);
        $user->role = UserRole::Admin;
        $user->email = 'participant-sections-admin-' . bin2hex(random_bytes(6)) . '@example.com';
        $user->loginType = UserLoginType::Email;
        $user->status = UserStatus::Open;
        $userRepository->persist($user);

        return $user;
    }
}
