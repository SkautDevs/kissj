<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\Event;
use kissj\User\UserRepository;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class AdminDashboardRoleCapsTest extends AppTestCase
{
    public function testDashboardRendersWithAllRoleCapsNull(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);
        $this->mutateEventForTest($app->getContainer(), $event, [
            'allowPatrols' => true,
            'allowIsts' => true,
            'allowTroops' => true,
            'allowGuests' => true,
            'allowOrganizingTeam' => true,
            'maximalClosedPatrolsCount' => null,
            'maximalClosedIstsCount' => null,
            'maximalClosedGuestsCount' => null,
            'maximalClosedOrganizingTeamCount' => null,
            'maximalClosedTroopLeadersCount' => null,
            'maximalClosedTroopParticipantsCount' => null,
        ]);
        $this->loginAdmin($app, $event);

        $body = $this->fetchDashboard($event);

        // the labels prove the role blocks actually rendered - without them the assertions
        // below would hold vacuously on a dashboard that skipped both sections
        self::assertStringContainsString('limit patrol: neomezeno</p>', $body);
        self::assertStringContainsString('limit servisáků: neomezeno</p>', $body);
        self::assertStringContainsString('limit skupin: neomezeno</p>', $body);
        self::assertStringContainsString('limit účastníků: neomezeno</p>', $body);
        // the guest and OT templates break the line between label and colon
        self::assertMatchesRegularExpression('~limit hostů\s*: neomezeno</p>~u', $body);
        self::assertMatchesRegularExpression('~Limit organizátorů\s*: neomezeno</p>~u', $body);
    }

    public function testDashboardRendersNumericRoleCaps(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);
        // all six are set, not just the two asserted on, so the assertStringNotContainsString
        // below cannot be broken by an unrelated role whose cap happens to be NULL
        $this->mutateEventForTest($app->getContainer(), $event, [
            'allowPatrols' => true,
            'allowIsts' => true,
            'allowTroops' => true,
            'allowGuests' => true,
            'allowOrganizingTeam' => true,
            'maximalClosedPatrolsCount' => 42,
            'maximalClosedIstsCount' => 7,
            'maximalClosedGuestsCount' => 3,
            'maximalClosedOrganizingTeamCount' => 5,
            'maximalClosedTroopLeadersCount' => 11,
            'maximalClosedTroopParticipantsCount' => 13,
        ]);
        $this->loginAdmin($app, $event);

        $body = $this->fetchDashboard($event);

        self::assertStringContainsString('limit patrol: 42</p>', $body);
        self::assertStringContainsString('limit servisáků: 7</p>', $body);
        self::assertStringContainsString('limit skupin: 11</p>', $body);
        self::assertStringContainsString('limit účastníků: 13</p>', $body);
        self::assertMatchesRegularExpression('~limit hostů\s*: 3</p>~u', $body);
        self::assertMatchesRegularExpression('~Limit organizátorů\s*: 5</p>~u', $body);
        self::assertStringNotContainsString('neomezeno', $body);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function loginAdmin(App $app, Event $event): void
    {
        $userRepository = $this->getService($app, UserRepository::class);
        $adminUser = $this->createAdminUser($app);
        $adminUser->status = UserStatus::Open;
        // createAdminUser() builds the admin against event 1, but LoggedOnlyMiddleware logs the
        // user out when their event does not match the event in the URL, so it must be repointed.
        $adminUser->event = $event;
        $userRepository->persist($adminUser);

        $_SESSION['user'] = ['id' => $adminUser->id];
    }

    private function fetchDashboard(Event $event): string
    {
        $response = $this->getTestApp(false)->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/admin/dashboard',
        ));
        self::assertSame(200, $response->getStatusCode());

        return (string)$response->getBody();
    }
}
