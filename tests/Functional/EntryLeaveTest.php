<?php

declare(strict_types=1);

namespace Tests\Functional;

use DateTimeInterface;
use kissj\Application\DateTimeUtils;
use kissj\Event\EventRepository;
use kissj\Participant\Ist\IstRepository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantService;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Participant\Patrol\PatrolParticipantRepository;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;
use Tests\AppTestCase;
use Throwable;

class EntryLeaveTest extends AppTestCase
{
    private const string TEST_EVENT_SECRET = 'test-api-secret-entry-leave';

    /** @var list<Participant> participants this test entered and that must be left again on teardown */
    private array $enteredParticipants = [];

    private ?ParticipantService $participantServiceForTeardown = null;

    protected function tearDown(): void
    {
        // The dev DB is never reset between runs, so any participant left "entered" here
        // would permanently inflate every future "currently present" query on its event.
        // This must run even when a test fails partway through, hence tearDown() rather
        // than a few lines at the end of each test method - and it must run before
        // parent::tearDown() closes the DB connection these calls rely on. setAsLeaved()
        // persists, which can genuinely throw, so the exception is caught and rethrown
        // after parent::tearDown() rather than using try/finally: PHPStan's
        // phpunit.callParent check only looks for a literal parent::tearDown() call as a
        // direct statement of this method, not one nested inside a finally block.
        $cleanupFailure = null;
        try {
            if ($this->participantServiceForTeardown !== null) {
                foreach ($this->enteredParticipants as $participant) {
                    $this->participantServiceForTeardown->setAsLeaved($participant);
                }
            }
        } catch (Throwable $throwable) {
            $cleanupFailure = $throwable;
        }

        parent::tearDown();

        if ($cleanupFailure !== null) {
            throw $cleanupFailure;
        }
    }

    private function trackForCleanup(ParticipantService $participantService, Participant $participant): void
    {
        $this->enteredParticipants[] = $participant;
        $this->participantServiceForTeardown = $participantService;
    }

    public function testGroupLeaveSkipsMembersWhoNeverEntered(): void
    {
        $app = $this->getTestApp();
        [$leader, $present, $neverEntered, $alreadyLeft] = $this->makePatrolInMixedStates($app);

        // baseline read back from the database, because a round-trip drops the Berlin offset
        $app = $this->getTestApp(false);
        $alreadyLeftStamp = $this->getService($app, PatrolParticipantRepository::class)
            ->get($alreadyLeft->id)->leaveDate?->format(DATE_ATOM);
        self::assertNotNull($alreadyLeftStamp);

        $app = $this->getTestApp(false);
        $response = $app->handle($this->leaveRequest('/v3/leave/group/' . $leader->id));

        self::assertSame(200, $response->getStatusCode());
        /** @var array{alteredParticipantIds: list<int>} $body */
        $body = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertEqualsCanonicalizing(
            [$leader->id, $present->id],
            $body['alteredParticipantIds'],
            'only participants who are on site may be checked out',
        );

        $app = $this->getTestApp(false);
        $plRepository = $this->getService($app, PatrolLeaderRepository::class);
        $ppRepository = $this->getService($app, PatrolParticipantRepository::class);

        self::assertNotNull($plRepository->get($leader->id)->leaveDate);
        self::assertNotNull($ppRepository->get($present->id)->leaveDate);
        self::assertNull(
            $ppRepository->get($neverEntered->id)->leaveDate,
            'a no-show must not be recorded as having left',
        );
        self::assertSame(
            $alreadyLeftStamp,
            $ppRepository->get($alreadyLeft->id)->leaveDate?->format(DATE_ATOM),
            'an already-left member keeps the original timestamp',
        );
    }

    public function testSecondGroupLeaveAltersNobody(): void
    {
        $app = $this->getTestApp();
        [$leader] = $this->makePatrolInMixedStates($app);

        $app = $this->getTestApp(false);
        $app->handle($this->leaveRequest('/v3/leave/group/' . $leader->id));

        $app = $this->getTestApp(false);
        $response = $app->handle($this->leaveRequest('/v3/leave/group/' . $leader->id));

        self::assertSame(200, $response->getStatusCode());
        /** @var array{alteredParticipantIds: list<int>} $body */
        $body = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame([], $body['alteredParticipantIds']);
    }

    public function testGroupLeaveRefusesParticipantWithoutGroup(): void
    {
        $app = $this->getTestApp();
        $ist = $this->makeIst($app);

        // the IST is on site, so individual leave must answer 200 - without this the 403 below
        // would also pass for an id the API cannot see at all, which is a different branch of
        // the same error message
        $app = $this->getTestApp(false);
        $individualResponse = $app->handle($this->leaveRequest('/v3/leave/participant/' . $ist->id));
        self::assertSame(200, $individualResponse->getStatusCode());

        $app = $this->getTestApp(false);
        $response = $app->handle($this->leaveRequest('/v3/leave/group/' . $ist->id));

        self::assertSame(403, $response->getStatusCode());
        self::assertStringContainsString('one or more participants not found', (string)$response->getBody());
    }

    public function testIndividualLeaveRefusesParticipantWhoNeverEntered(): void
    {
        $app = $this->getTestApp();
        [, , $neverEntered] = $this->makePatrolInMixedStates($app);

        $app = $this->getTestApp(false);
        $response = $app->handle($this->leaveRequest('/v3/leave/participant/' . $neverEntered->id));

        self::assertSame(403, $response->getStatusCode());
        self::assertStringContainsString('participant not entered', (string)$response->getBody());

        $app = $this->getTestApp(false);
        $ppRepository = $this->getService($app, PatrolParticipantRepository::class);
        self::assertNull($ppRepository->get($neverEntered->id)->leaveDate);
    }

    public function testIndividualLeaveStaysIdempotentForAlreadyLeftParticipant(): void
    {
        $app = $this->getTestApp();
        [, , , $alreadyLeft] = $this->makePatrolInMixedStates($app);

        // baseline read back from the database, because a round-trip drops the Berlin offset
        $app = $this->getTestApp(false);
        $stamp = $this->getService($app, PatrolParticipantRepository::class)
            ->get($alreadyLeft->id)->leaveDate?->format(DATE_ATOM);
        self::assertNotNull($stamp);

        $app = $this->getTestApp(false);
        $response = $app->handle($this->leaveRequest('/v3/leave/participant/' . $alreadyLeft->id));

        self::assertSame(200, $response->getStatusCode());

        $app = $this->getTestApp(false);
        $ppRepository = $this->getService($app, PatrolParticipantRepository::class);
        self::assertSame($stamp, $ppRepository->get($alreadyLeft->id)->leaveDate?->format(DATE_ATOM));
    }

    // the already-left check must stay ahead of the entryDate guard, so legacy rows carrying a
    // leaveDate without an entryDate keep answering idempotently instead of starting to 403.
    // Unlike the test above, this fixture has only one of the two dates, so it fails if the two
    // early returns are swapped
    public function testIndividualLeaveStaysIdempotentForLegacyRowLeftWithoutEntry(): void
    {
        $app = $this->getTestApp();
        [, , , , $legacyLeft] = $this->makePatrolInMixedStates($app);

        // baseline read back from the database, because a round-trip drops the Berlin offset
        $app = $this->getTestApp(false);
        $legacyLeftFromDb = $this->getService($app, PatrolParticipantRepository::class)->get($legacyLeft->id);
        $stamp = $legacyLeftFromDb->leaveDate?->format(DATE_ATOM);
        self::assertNotNull($stamp);
        self::assertNull($legacyLeftFromDb->entryDate, 'fixture must carry a leaveDate without an entryDate');

        $app = $this->getTestApp(false);
        $response = $app->handle($this->leaveRequest('/v3/leave/participant/' . $legacyLeft->id));

        self::assertSame(200, $response->getStatusCode());

        $app = $this->getTestApp(false);
        $ppRepository = $this->getService($app, PatrolParticipantRepository::class);
        self::assertSame($stamp, $ppRepository->get($legacyLeft->id)->leaveDate?->format(DATE_ATOM));
    }

    private function leaveRequest(string $path): ServerRequestInterface
    {
        return (new ServerRequestFactory())
            ->createServerRequest('POST', $path)
            ->withHeader('Authorization', 'Bearer ' . self::TEST_EVENT_SECRET);
    }

    /**
     * A patrol covering every state the guard has to distinguish: leader on site, one member on
     * site, one who never arrived, one already checked out, and one legacy row that carries a
     * leaveDate without an entryDate - the shape that pins the order of the two early returns
     * in leaveParticipantFromWebApp().
     *
     * @param App<ContainerInterface> $app
     * @return array{
     *     0: PatrolLeader,
     *     1: PatrolParticipant,
     *     2: PatrolParticipant,
     *     3: PatrolParticipant,
     *     4: PatrolParticipant,
     * }
     */
    private function makePatrolInMixedStates(App $app): array
    {
        $eventRepository = $this->getService($app, EventRepository::class);
        $userService = $this->getService($app, UserService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $plRepository = $this->getService($app, PatrolLeaderRepository::class);
        $ppRepository = $this->getService($app, PatrolParticipantRepository::class);
        $participantService = $this->getService($app, ParticipantService::class);

        $event = $this->getSmallTestEvent($eventRepository);
        $this->mutateEventForTest($app->getContainer(), $event, ['apiKeyEntry' => self::TEST_EVENT_SECRET]);

        $leaderEmail = 'entry-leave-leader-' . bin2hex(random_bytes(4)) . '@example.com';
        $leaderUser = $userService->registerEmailUser($leaderEmail, $event);
        $leaderParticipant = $userService->createParticipantSetRole($leaderUser, 'pl');
        $leader = $plRepository->get($leaderParticipant->id);
        $leader->firstName = 'Leader';
        $leader->lastName = 'OnSite';
        $leader->entryDate = DateTimeUtils::getDateTime();
        $plRepository->persist($leader);
        $this->trackForCleanup($participantService, $leader);

        $leaderUser->status = UserStatus::Paid;
        $userRepository->persist($leaderUser);

        $present = $this->makeMember($ppRepository, $leader, 'Present', DateTimeUtils::getDateTime(), null);
        $this->trackForCleanup($participantService, $present);

        $neverEntered = $this->makeMember($ppRepository, $leader, 'NoShow', null, null);
        $alreadyLeft = $this->makeMember(
            $ppRepository,
            $leader,
            'Gone',
            DateTimeUtils::getDateTime('2026-08-01 08:00:00'),
            DateTimeUtils::getDateTime('2026-08-01 18:00:00'),
        );
        $legacyLeftWithoutEntry = $this->makeMember(
            $ppRepository,
            $leader,
            'Legacy',
            null,
            DateTimeUtils::getDateTime('2026-08-01 19:00:00'),
        );

        return [$leader, $present, $neverEntered, $alreadyLeft, $legacyLeftWithoutEntry];
    }

    private function makeMember(
        PatrolParticipantRepository $ppRepository,
        PatrolLeader $leader,
        string $lastName,
        ?DateTimeInterface $entryDate,
        ?DateTimeInterface $leaveDate,
    ): PatrolParticipant {
        $member = new PatrolParticipant();
        $member->patrolLeader = $leader;
        $member->firstName = 'Member';
        $member->lastName = $lastName;
        $member->entryDate = $entryDate;
        $member->leaveDate = $leaveDate;
        $ppRepository->persist($member);

        return $member;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function makeIst(App $app): Participant
    {
        $eventRepository = $this->getService($app, EventRepository::class);
        $userService = $this->getService($app, UserService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $istRepository = $this->getService($app, IstRepository::class);
        $participantService = $this->getService($app, ParticipantService::class);

        $event = $this->getSmallTestEvent($eventRepository);
        $this->mutateEventForTest($app->getContainer(), $event, ['apiKeyEntry' => self::TEST_EVENT_SECRET]);

        $email = 'entry-leave-ist-' . bin2hex(random_bytes(4)) . '@example.com';
        $user = $userService->registerEmailUser($email, $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');
        $ist = $istRepository->get($participant->id);
        $ist->firstName = 'Solo';
        $ist->lastName = 'Servisak';
        $ist->entryDate = DateTimeUtils::getDateTime();
        $istRepository->persist($ist);

        $user->status = UserStatus::Paid;
        $userRepository->persist($user);
        $this->trackForCleanup($participantService, $ist);

        return $istRepository->get($ist->id);
    }
}
