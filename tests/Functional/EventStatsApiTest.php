<?php

declare(strict_types=1);

namespace Tests\Functional;

use DateTimeImmutable;
use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\User\UserRepository;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App;
use Tests\AppTestCase;

class EventStatsApiTest extends AppTestCase
{
    public function testStatsReportsCapacityForEnabledRoles(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokStatsEvent($app);
        $this->mutateEventForTest($app->getContainer(), $event, [
            'allowTroops' => true,
            'allowIsts' => true,
            'allowPatrols' => false,
            'allowGuests' => false,
            'maximalClosedParticipantsCount' => 50,
            'maximalClosedIstsCount' => 10,
            'maximalClosedTroopLeadersCount' => null,
            'maximalClosedTroopParticipantsCount' => 20,
        ]);
        $this->createIstWithStatus($app, $event, UserStatus::Open);
        $this->createIstWithStatus($app, $event, UserStatus::Closed);
        $this->createIstWithStatus($app, $event, UserStatus::Paid);
        $this->createParticipantWithStatus($app, $event, 'guest', UserStatus::Closed);

        $response = $this->getStats($app, 'stats-api-obrok');
        $json = $this->decode($response);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(['total', 'troops', 'ists'], array_keys($json['capacity']));
        self::assertSame(['used' => 2, 'max' => 10], $json['capacity']['ists']);
        self::assertSame(['used' => 0, 'max' => null], $json['capacity']['troops']);
        self::assertSame(['used' => 3, 'max' => 50], $json['capacity']['total']);
        self::assertArrayNotHasKey('guests', $json['capacity']);
        self::assertSame(['name' => $event->readableName], $json['event']);
        self::assertArrayNotHasKey('paid', $json);
    }

    public function testTroopsCountBothTroopRolesAgainstSummedCaps(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokStatsEvent($app);
        $this->mutateEventForTest($app->getContainer(), $event, [
            'allowTroops' => true,
            'maximalClosedTroopLeadersCount' => 5,
            'maximalClosedTroopParticipantsCount' => 30,
        ]);
        $this->createParticipantWithStatus($app, $event, 'tl', UserStatus::Closed);
        $this->createParticipantWithStatus($app, $event, 'tp', UserStatus::Paid);
        $this->createParticipantWithStatus($app, $event, 'tp', UserStatus::Open);

        $json = $this->decode($this->getStats($app, 'stats-api-obrok'));

        self::assertSame(['used' => 2, 'max' => 35], $json['capacity']['troops']);
        self::assertArrayNotHasKey('troopLeaders', $json['capacity']);
        self::assertArrayNotHasKey('troopParticipants', $json['capacity']);
    }

    public function testStatsSendsPublicJsonHeaders(): void
    {
        $app = $this->getTestApp();
        $this->getObrokStatsEvent($app);

        $response = $this->getStats($app, 'stats-api-obrok');
        $json = $this->decode($response);

        self::assertSame('application/json', $response->getHeaderLine('Content-Type'));
        self::assertSame('*', $response->getHeaderLine('Access-Control-Allow-Origin'));
        self::assertSame('public, max-age=60', $response->getHeaderLine('Cache-Control'));
        self::assertInstanceOf(
            DateTimeImmutable::class,
            DateTimeImmutable::createFromFormat(DATE_ATOM, $json['generatedAt']),
        );
    }

    public function testTotalMaxIsNullWithoutCap(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokStatsEvent($app);
        $this->mutateEventForTest($app->getContainer(), $event, ['maximalClosedParticipantsCount' => null]);

        $json = $this->decode($this->getStats($app, 'stats-api-obrok'));

        self::assertArrayHasKey('max', $json['capacity']['total']);
        self::assertNull($json['capacity']['total']['max']);
    }

    public function testEnteredCountsPaidParticipantsIncludingThoseWhoLeft(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokStatsEvent($app);
        $this->mutateEventForTest($app->getContainer(), $event, ['allowIsts' => true]);
        $now = DateTimeUtils::getDateTime();
        $this->createIstWithStatus($app, $event, UserStatus::Paid, $now);
        $this->createIstWithStatus($app, $event, UserStatus::Paid, $now, $now);
        $this->createIstWithStatus($app, $event, UserStatus::Paid);
        $this->createIstWithStatus($app, $event, UserStatus::Closed, $now);
        $this->createPatrolWithEnteredMember($app, $event, UserStatus::Paid, $now);
        $this->createPatrolWithEnteredMember($app, $event, UserStatus::Closed, $now);

        $json = $this->decode($this->getStats($app, 'stats-api-obrok'));

        self::assertSame(3, $json['entered']);
    }

    public function testContingentCappedEventReportsNullRoleMax(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTypedTestEvent($app, 'cej', 'stats-api-cej');
        $this->mutateEventForTest($app->getContainer(), $event, [
            'allowIsts' => true,
            'maximalClosedIstsCount' => 10,
            'maximalClosedParticipantsCount' => 100,
        ]);
        $this->createIstWithStatus($app, $event, UserStatus::Closed, null, null, 'detail.contingent.poland');
        $this->createIstWithStatus($app, $event, UserStatus::Closed, null, null, 'detail.contingent.slovakia');

        $json = $this->decode($this->getStats($app, 'stats-api-cej'));

        self::assertSame(2, $json['capacity']['ists']['used']);
        self::assertNull($json['capacity']['ists']['max']);
        self::assertSame(100, $json['capacity']['total']['max']);
    }

    public function testStatsIgnoresLoggedInUserOfAnotherEvent(): void
    {
        $app = $this->getTestApp();
        $this->getObrokStatsEvent($app);
        $foreignParticipant = $this->createParticipantWithRole($app->getContainer(), 'ist');
        $_SESSION['user'] = ['id' => $foreignParticipant->getUserButNotNull()->id];

        $response = $this->getStats($this->getTestApp(false), 'stats-api-obrok');

        self::assertSame(200, $response->getStatusCode());
        self::assertArrayHasKey('user', $_SESSION);
    }

    public function testStatsForUnknownSlugReturnsNotFound(): void
    {
        $response = $this->getTestApp()->handle($this->createRequest('/v3/event/no-such-event/stats'));

        self::assertSame(404, $response->getStatusCode());
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function getObrokStatsEvent(App $app): Event
    {
        return $this->getTypedTestEvent($app, 'obrok', 'stats-api-obrok');
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createIstWithStatus(
        App $app,
        Event $event,
        UserStatus $status,
        ?DateTimeImmutable $entryDate = null,
        ?DateTimeImmutable $leaveDate = null,
        ?string $contingent = null,
    ): void {
        $participant = $this->createParticipantWithStatus($app, $event, 'ist', $status);
        $participant->entryDate = $entryDate;
        $participant->leaveDate = $leaveDate;
        $participant->contingent = $contingent;
        $this->getService($app, ParticipantRepository::class)->persist($participant);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createParticipantWithStatus(App $app, Event $event, string $role, UserStatus $status): Participant
    {
        $participant = $this->createParticipantWithRole($app->getContainer(), $role, $event);
        $user = $participant->getUserButNotNull();
        $user->status = $status;
        $this->getService($app, UserRepository::class)->persist($user);

        return $participant;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createPatrolWithEnteredMember(
        App $app,
        Event $event,
        UserStatus $leaderStatus,
        DateTimeImmutable $entryDate,
    ): void {
        $leader = $this->createParticipantWithStatus($app, $event, 'pl', $leaderStatus);
        $member = new PatrolParticipant();
        $member->patrolLeader = $this->getService($app, PatrolLeaderRepository::class)->get($leader->id);
        $member->entryDate = $entryDate;
        $this->getService($app, ParticipantRepository::class)->persist($member);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function getStats(App $app, string $slug): Response
    {
        return $app->handle($this->createRequest('/v3/event/' . $slug . '/stats'));
    }

    /**
     * @return array{
     *     event: array{name: string},
     *     capacity: array<string, array{used: int, max: int|null}>,
     *     entered: int,
     *     generatedAt: string
     * }
     */
    private function decode(Response $response): array
    {
        /**
         * @var array{
         *     event: array{name: string},
         *     capacity: array<string, array{used: int, max: int|null}>,
         *     entered: int,
         *     generatedAt: string
         * } $json
         */
        $json = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return $json;
    }
}
