<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\TechnicalTeam\TechnicalTeamLeaderRepository;
use kissj\Participant\TechnicalTeam\TechnicalTeamParticipant;
use kissj\Participant\TechnicalTeam\TechnicalTeamParticipantRepository;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class TechnicalTeamAdminPageTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-slug';
    private const string BASE_URL = '/v2/event/' . self::TEST_EVENT_SLUG;

    public function testPageShowsRegistrationUrlWithToken(): void
    {
        $app = $this->getTestApp();
        $this->enableTechnicalTeams($app);
        $this->loginAdmin($app);

        $response = $this->getTestApp(false)->handle($this->createRequest(self::BASE_URL . '/admin/technical-teams'));

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('ttl_token=', (string)$response->getBody());
    }

    public function testRegenerateChangesToken(): void
    {
        $app = $this->getTestApp();
        $event = $this->enableTechnicalTeams($app, 'old-token-' . bin2hex(random_bytes(4)));
        $previousToken = $event->technicalTeamRegistrationToken;
        $this->loginAdmin($app);

        $response = $this->getTestApp(false)->handle($this->createRequest(
            self::BASE_URL . '/admin/technical-teams/regenerateToken',
            'POST',
        ));

        self::assertSame(302, $response->getStatusCode());
        $freshEvent = $this->getTestSlugEvent($this->getService($this->getTestApp(false), EventRepository::class));
        self::assertNotNull($freshEvent->technicalTeamRegistrationToken);
        self::assertNotSame($previousToken, $freshEvent->technicalTeamRegistrationToken);
    }

    public function testTableShowsTeamNameHeadsAndTotals(): void
    {
        $app = $this->getTestApp();
        $this->enableTechnicalTeams($app);
        $this->loginAdmin($app);
        [$openBefore, $closedBefore] = $this->fetchTotals();

        $suffix = bin2hex(random_bytes(4));
        $this->createTeam('Hasici' . $suffix, 2, UserStatus::Open, 'ttl-admin-open-' . $suffix . '@example.com');
        $this->createTeam('Catering' . $suffix, 1, UserStatus::Paid, 'ttl-admin-paid-' . $suffix . '@example.com');

        $this->loginAdmin($this->getTestApp(false));
        $body = $this->fetchBody(self::BASE_URL . '/admin/technical-teams');
        [$openAfter, $closedAfter] = $this->extractTotals($body);

        self::assertStringContainsString('Hasici' . $suffix, $body);
        self::assertStringContainsString('Catering' . $suffix, $body);
        self::assertStringContainsString('<td>3</td>', $body);
        self::assertStringContainsString('<td>2</td>', $body);
        self::assertSame($openBefore + 3, $openAfter);
        self::assertSame($closedBefore + 2, $closedAfter);
    }

    public function testDashboardLinkOnlyWhenEnabled(): void
    {
        $app = $this->getTestApp();
        $this->enableTechnicalTeams($app);
        $this->loginAdmin($app);

        self::assertStringContainsString(
            '/admin/technical-teams',
            $this->fetchBody(self::BASE_URL . '/admin/dashboard'),
        );

        $app = $this->getTestApp(false);
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $this->mutateEventForTest($app->getContainer(), $event, ['allowTechnicalTeams' => false]);

        self::assertStringNotContainsString(
            '/admin/technical-teams',
            $this->fetchBody(self::BASE_URL . '/admin/dashboard'),
        );
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function enableTechnicalTeams(App $app, ?string $token = null): Event
    {
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $fields = ['allowTechnicalTeams' => true];
        if ($token !== null) {
            $fields['technicalTeamRegistrationToken'] = $token;
        }
        $this->mutateEventForTest($app->getContainer(), $event, $fields);

        return $this->getTestSlugEvent($this->getService($app, EventRepository::class));
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function loginAdmin(App $app): void
    {
        $adminUser = $this->createAdminUser($app);
        $adminUser->status = UserStatus::Open;
        $adminUser->event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $this->getService($app, UserRepository::class)->persist($adminUser);

        $_SESSION['user'] = ['id' => $adminUser->id];
    }

    private function fetchBody(string $path): string
    {
        $response = $this->getTestApp(false)->handle($this->createRequest($path));
        self::assertSame(200, $response->getStatusCode());

        return (string)$response->getBody();
    }

    /**
     * @return array{int, int}
     */
    private function fetchTotals(): array
    {
        return $this->extractTotals($this->fetchBody(self::BASE_URL . '/admin/technical-teams'));
    }

    /**
     * @return array{int, int}
     */
    private function extractTotals(string $body): array
    {
        if (preg_match_all('~<p>[^<]*: <b>(\d+)</b></p>~', $body, $matches) === 0) {
            return [0, 0];
        }

        return [(int)$matches[1][0], (int)$matches[1][1]];
    }

    private function createTeam(string $teamName, int $memberCount, UserStatus $status, string $email): void
    {
        $app = $this->getTestApp(false);
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $userService = $this->getService($app, UserService::class);
        $user = $userService->registerEmailUser($email, $event);
        $participant = $userService->createParticipantSetRole($user, 'ttl');

        $leaderRepository = $this->getService($app, TechnicalTeamLeaderRepository::class);
        $leader = $leaderRepository->get($participant->id);
        $leader->technicalTeamName = $teamName;
        $leaderRepository->persist($leader);

        $memberRepository = $this->getService($app, TechnicalTeamParticipantRepository::class);
        for ($i = 0; $i < $memberCount; $i++) {
            $member = new TechnicalTeamParticipant();
            $member->technicalTeamLeader = $leader;
            $member->firstName = 'Member' . $i;
            $member->lastName = 'Test';
            $memberRepository->persist($member);
        }

        $user->status = $status;
        $this->getService($app, UserRepository::class)->persist($user);
    }
}
