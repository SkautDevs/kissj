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

    public function testZeroCapacityRendersAsZeroNotBlank(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $this->getTestSlugEvent($eventRepository);
        // a stored 0 means "role closed"; if the input renders blank it round-trips to NULL
        // on the next save, which now means unlimited - the opposite setting
        $this->mutateEventForTest($app->getContainer(), $event, ['maximalClosedIstsCount' => 0]);
        $this->loginAsEventAdmin($app->getContainer());

        $body = $this->fetchRoleManagement();

        // the name and the value must be matched together - a bare value="0" also matches the
        // unrelated troop min/max inputs, which render 0 regardless of this fix
        self::assertMatchesRegularExpression('/name="maximalClosedIstsCount"\s+value="0"/', $body);
    }

    public function testNullCapacityRendersUnlimitedLabel(): void
    {
        $body = $this->fetchRoleManagementWithCaps(['maximalClosedIstsCount' => null]);

        // every other cap is numeric, so the IST card is the only unlimited one and its two
        // labels (event type max + DB max) are the page's only occurrences of the word
        self::assertSame(2, substr_count($body, 'neomezeno'));
    }

    public function testNullTotalCapacityRendersUnlimitedLabel(): void
    {
        $body = $this->fetchRoleManagementWithCaps(['maximalClosedParticipantsCount' => null]);

        // only the total block is unlimited here, and it prints the same label twice
        self::assertSame(2, substr_count($body, 'neomezeno'));
        // the input itself must stay blank - rendering 0 would round-trip an unlimited event
        // back to "closed" on the next save
        self::assertMatchesRegularExpression('/name="maximalClosedParticipantsCount"\s+value=""/', $body);
    }

    public function testNullPerPatrolMaximumRendersBlankNotZero(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $this->getTestSlugEvent($eventRepository);
        // rendering NULL as 0 would post 0 back, and 0 now means "at most 0 members"
        $this->mutateEventForTest($app->getContainer(), $event, ['maximalPatrolParticipantsCount' => null]);
        $this->loginAsEventAdmin($app->getContainer());

        $body = $this->fetchRoleManagement();

        self::assertMatchesRegularExpression('/name="maximalPatrolParticipantsCount"\s+value=""/', $body);
    }

    public function testNumericPerPatrolMaximumStillRenders(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $this->getTestSlugEvent($eventRepository);
        $this->mutateEventForTest($app->getContainer(), $event, ['maximalPatrolParticipantsCount' => 8]);
        $this->loginAsEventAdmin($app->getContainer());

        $body = $this->fetchRoleManagement();

        self::assertMatchesRegularExpression('/name="maximalPatrolParticipantsCount"\s+value="8"/', $body);
    }

    // distinct numbers for every cap the page renders, so a test can null exactly one of them
    // and count the unlimited labels without a second role muddying the result
    private const array NUMERIC_CAPS = [
        'maximalClosedParticipantsCount' => 500,
        'maximalClosedPatrolsCount' => 41,
        'maximalPatrolParticipantsCount' => 42,
        'maximalClosedTroopLeadersCount' => 43,
        'maximalClosedTroopParticipantsCount' => 44,
        'maximalClosedIstsCount' => 45,
        'maximalClosedGuestsCount' => 46,
        'maximalClosedOrganizingTeamCount' => 47,
    ];

    /**
     * @param array<string, int|null> $capOverrides
     */
    private function fetchRoleManagementWithCaps(array $capOverrides): string
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $this->getTestSlugEvent($eventRepository);
        $this->mutateEventForTest($app->getContainer(), $event, $capOverrides + self::NUMERIC_CAPS);
        $this->loginAsEventAdmin($app->getContainer());

        return $this->fetchRoleManagement();
    }

    private function fetchRoleManagement(): string
    {
        $response = $this->getTestApp(false)->handle($this->createRequest(self::ROLE_MANAGEMENT_URL));
        self::assertSame(200, $response->getStatusCode());

        return (string)$response->getBody();
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
