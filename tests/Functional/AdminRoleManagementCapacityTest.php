<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\User\User;
use kissj\User\UserLoginType;
use kissj\User\UserRepository;
use kissj\User\UserRole;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Tests\AppTestCase;

class AdminRoleManagementCapacityTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-slug';
    private const string ROLE_MANAGEMENT_URL = '/v2/event/' . self::TEST_EVENT_SLUG . '/admin/roleManagement';

    public function testCapacityAboveDatabaseRangeIsRejected(): void
    {
        $this->assertTotalCapacityRejected('100000000000');
    }

    public function testNonNumericCapacityIsRejected(): void
    {
        $this->assertTotalCapacityRejected('abc');
    }

    public function testNegativeCapacityIsRejected(): void
    {
        $this->assertTotalCapacityRejected('-5');
    }

    public function testValidCapacityIsSaved(): void
    {
        $app = $this->getTestApp();
        $this->loginAsEventAdmin($app->getContainer());

        $response = $this->postTotalCapacity('500');

        self::assertSame(302, $response->getStatusCode());
        self::assertSame(500, $this->readTotalCapacity());
    }

    private function assertTotalCapacityRejected(string $submittedValue): void
    {
        $app = $this->getTestApp();
        $this->loginAsEventAdmin($app->getContainer());

        $capacityBefore = $this->readTotalCapacity();

        $response = $this->postTotalCapacity($submittedValue);

        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString('roleManagement', $response->getHeaderLine('Location'));
        self::assertSame($capacityBefore, $this->readTotalCapacity());
    }

    private function postTotalCapacity(string $submittedValue): ResponseInterface
    {
        return $this->getTestApp(false)->handle($this->createRequest(
            self::ROLE_MANAGEMENT_URL,
            'POST',
            [
                'role' => 'total',
                'maximalClosedParticipantsCount' => $submittedValue,
            ],
        ));
    }

    private function readTotalCapacity(): ?int
    {
        $eventRepository = $this->getService($this->getTestApp(false), EventRepository::class);

        return $this->getTestSlugEvent($eventRepository)->maximalClosedParticipantsCount;
    }

    private function loginAsEventAdmin(ContainerInterface $container): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);

        $user = new User();
        $user->event = $this->getTestSlugEvent($eventRepository);
        $user->role = UserRole::Admin;
        $user->email = 'admin-capacity-' . bin2hex(random_bytes(4)) . '@example.com';
        $user->loginType = UserLoginType::Email;
        $user->status = UserStatus::Open;
        $userRepository->persist($user);

        $_SESSION['user'] = ['id' => $user->id];
    }
}
