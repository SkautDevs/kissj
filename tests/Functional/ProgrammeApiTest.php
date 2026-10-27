<?php

declare(strict_types=1);

namespace Tests\Functional;

use ArrayObject;
use Dibi\Event as DibiEvent;
use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Participant\Patrol\PatrolParticipantRepository;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeRegistration;
use kissj\Programme\ProgrammeRegistrationRepository;
use kissj\Programme\ProgrammeRepository;
use kissj\Programme\ProgrammeSection;
use kissj\Programme\ProgrammeSectionRepository;
use kissj\User\UserRepository;
use kissj\User\UserStatus;
use LeanMapper\Connection;
use LogicException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Tests\AppTestCase;

class ProgrammeApiTest extends AppTestCase
{
    private const string EVENT_SLUG = 'test-event-slug';
    private const string API_KEY = 'programme-api-test-key';
    private const string EMPTY_EVENT_SLUG = 'programme-api-empty-event';
    private const string EMPTY_EVENT_API_KEY = 'programme-api-empty-event-key';
    private const string OTHER_EVENT_SLUG = 'programme-api-other-event';
    private const string QUERY_COUNT_EVENT_SLUG = 'programme-api-query-count-event';
    private const string QUERY_COUNT_EVENT_API_KEY = 'programme-api-query-count-event-key';

    public function testListIsEmptyForEventWithoutProgrammes(): void
    {
        $app = $this->getTestApp();
        $emptyEvent = $this->getTypedTestEvent($app, 'default', self::EMPTY_EVENT_SLUG);
        $this->mutateEventForTest($app->getContainer(), $emptyEvent, ['apiKeyEvent' => self::EMPTY_EVENT_API_KEY]);

        $response = $app->handle($this->createApiRequest('/v3/programme/list', self::EMPTY_EVENT_API_KEY));

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('Content-Type'));
        self::assertSame('{"sections":[],"programmes":[]}', (string)$response->getBody());
    }

    public function testListContainsSectionsInOrderAndProgrammesIncludingPreregistered(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $event = $this->getAuthorizedEvent($app);
        $sectionRepository = $this->getService($app, ProgrammeSectionRepository::class);

        $firstCreated = $this->createProgrammeSection($container, $event, 'api list first created');
        $secondCreated = $this->createProgrammeSection($container, $event, 'api list second created');
        $firstCreated->sortOrder = 1001;
        $secondCreated->sortOrder = 1000;
        $secondCreated->subtitle = '1. blok';
        $sectionRepository->persist($firstCreated);
        $sectionRepository->persist($secondCreated);

        $late = $this->createProgramme($app, $event, $firstCreated, 'api list late', '2027-06-03 10:00');
        $early = $this->createProgramme($app, $event, $secondCreated, 'api list early', '2027-06-03 08:00');
        $earlyTwin = $this->createProgramme($app, $event, $secondCreated, 'api list early twin', '2027-06-03 08:00');
        $preregistered = $this->createProgramme(
            $app,
            $event,
            $firstCreated,
            'api list preregistered',
            '2027-06-03 09:00',
            true,
        );
        $deleted = $this->createProgramme($app, $event, $firstCreated, 'api list deleted', '2027-06-03 09:00');
        $deleted->deletedAt = DateTimeUtils::getDateTime();
        $this->getService($app, ProgrammeRepository::class)->persist($deleted);

        $otherEvent = $this->getTypedTestEvent($app, 'default', self::OTHER_EVENT_SLUG);
        $foreign = $this->createProgramme(
            $app,
            $otherEvent,
            $this->createProgrammeSection($container, $otherEvent, 'api list foreign section'),
            'api list foreign',
            '2027-06-03 09:00',
        );

        $response = $app->handle($this->createApiRequest('/v3/programme/list', self::API_KEY));

        self::assertSame(200, $response->getStatusCode());
        $body = $this->decodeJson($response);
        self::assertIsArray($body['sections']);
        self::assertIsArray($body['programmes']);

        $sectionIds = array_column($body['sections'], 'id');
        self::assertLessThan(
            array_search($firstCreated->id, $sectionIds, true),
            array_search($secondCreated->id, $sectionIds, true),
        );
        $sectionKey = array_search($secondCreated->id, $sectionIds, true);
        self::assertIsInt($sectionKey);
        self::assertSame([
            'id' => $secondCreated->id,
            'name' => 'api list second created',
            'subtitle' => '1. blok',
        ], $body['sections'][$sectionKey]);

        $programmeIds = array_column($body['programmes'], 'id');
        self::assertNotContains($deleted->id, $programmeIds);
        self::assertNotContains($foreign->id, $programmeIds);
        $ownIds = [$late->id, $early->id, $earlyTwin->id, $preregistered->id];
        $ownIdsInResponseOrder = array_values(array_filter(
            $programmeIds,
            static fn (mixed $id): bool => in_array($id, $ownIds, true),
        ));
        self::assertSame([$early->id, $earlyTwin->id, $preregistered->id, $late->id], $ownIdsInResponseOrder);

        $preregisteredKey = array_search($preregistered->id, $programmeIds, true);
        self::assertIsInt($preregisteredKey);
        self::assertSame([
            'id' => $preregistered->id,
            'name' => 'api list preregistered',
            'sectionId' => $firstCreated->id,
            'description' => "description of api list preregistered\nsecond line",
            'place' => 'main field',
            'start' => '2027-06-03T09:00:00+02:00',
            'end' => '2027-06-03T11:00:00+02:00',
            'isPreregistered' => true,
            'targetRoles' => ['ist', 'guest'],
        ], $body['programmes'][$preregisteredKey]);
    }

    public function testListQueryCountDoesNotGrowWithProgrammeCount(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $event = $this->getTypedTestEvent($app, 'default', self::QUERY_COUNT_EVENT_SLUG);
        $this->mutateEventForTest($container, $event, ['apiKeyEvent' => self::QUERY_COUNT_EVENT_API_KEY]);
        $sections = [
            $this->createProgrammeSection($container, $event, 'api query count first'),
            $this->createProgrammeSection($container, $event, 'api query count second'),
        ];
        $queryCounter = $this->attachQueryCounter($app);
        $request = $this->createApiRequest('/v3/programme/list', self::QUERY_COUNT_EVENT_API_KEY);

        foreach ([0, 1] as $index) {
            $this->createProgramme($app, $event, $sections[$index], 'api query count ' . $index, '2027-06-06 08:00');
        }
        $queriesWithTwoProgrammes = $this->countQueriesOf($app, $queryCounter, $request);

        foreach ([2, 3, 4] as $index) {
            $section = $sections[$index % 2];
            $this->createProgramme($app, $event, $section, 'api query count ' . $index, '2027-06-06 09:00');
        }
        $queriesWithFiveProgrammes = $this->countQueriesOf($app, $queryCounter, $request);

        $response = $app->handle($request);
        self::assertSame(200, $response->getStatusCode());
        $programmes = $this->decodeJson($response)['programmes'];
        self::assertIsArray($programmes);
        self::assertCount(5, $programmes);
        self::assertSame($queriesWithTwoProgrammes, $queriesWithFiveProgrammes);
    }

    public function testParticipantByTieCodeQueryCountDoesNotGrowWithRegistrations(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $event = $this->getTypedTestEvent($app, 'default', self::QUERY_COUNT_EVENT_SLUG);
        $this->mutateEventForTest($container, $event, ['apiKeyEvent' => self::QUERY_COUNT_EVENT_API_KEY]);
        $sections = [
            $this->createProgrammeSection($container, $event, 'api tie query count first'),
            $this->createProgrammeSection($container, $event, 'api tie query count second'),
        ];
        $participant = $this->createPaidIstOnEvent($app, $event, 'Counted');
        $queryCounter = $this->attachQueryCounter($app);
        $request = $this->createApiRequest(
            '/v3/programme/participant/tie/' . $participant->tieCode,
            self::QUERY_COUNT_EVENT_API_KEY,
        );

        foreach ([0, 1] as $index) {
            $programme = $this->createProgramme(
                $app,
                $event,
                $sections[$index],
                'api tie count ' . $index,
                '2027-06-07 08:00',
            );
            $this->register($app, $programme, $participant);
        }
        $queriesWithTwoRegistrations = $this->countQueriesOf($app, $queryCounter, $request);

        foreach ([2, 3, 4] as $index) {
            $programme = $this->createProgramme(
                $app,
                $event,
                $sections[$index % 2],
                'api tie count ' . $index,
                '2027-06-07 09:00',
            );
            $this->register($app, $programme, $participant);
        }
        $queriesWithFiveRegistrations = $this->countQueriesOf($app, $queryCounter, $request);

        $response = $app->handle($request);
        self::assertSame(200, $response->getStatusCode());
        $programmes = $this->decodeJson($response)['programmes'];
        self::assertIsArray($programmes);
        self::assertCount(5, $programmes);
        self::assertSame($queriesWithTwoRegistrations, $queriesWithFiveRegistrations);
    }

    public function testProgrammeParticipantsQueryCountDoesNotGrowWithParticipants(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $event = $this->getTypedTestEvent($app, 'default', self::QUERY_COUNT_EVENT_SLUG);
        $this->mutateEventForTest($container, $event, ['apiKeyEvent' => self::QUERY_COUNT_EVENT_API_KEY]);
        $section = $this->createProgrammeSection($container, $event, 'api participants query count');
        $programme = $this->createProgramme($app, $event, $section, 'api participants count', '2027-06-08 08:00');
        $queryCounter = $this->attachQueryCounter($app);
        $request = $this->createApiRequest(
            '/v3/programme/' . $programme->id . '/participants',
            self::QUERY_COUNT_EVENT_API_KEY,
        );

        foreach ([0, 1] as $index) {
            $this->register($app, $programme, $this->createPaidIstOnEvent($app, $event, 'Counted ' . $index));
        }
        $queriesWithTwoParticipants = $this->countQueriesOf($app, $queryCounter, $request);

        foreach ([2, 3, 4] as $index) {
            $this->register($app, $programme, $this->createPaidIstOnEvent($app, $event, 'Counted ' . $index));
        }
        $queriesWithFiveParticipants = $this->countQueriesOf($app, $queryCounter, $request);

        $response = $app->handle($request);
        self::assertSame(200, $response->getStatusCode());
        $tieCodes = $this->decodeJson($response)['tieCodes'];
        self::assertIsArray($tieCodes);
        self::assertCount(5, $tieCodes);
        self::assertSame($queriesWithTwoParticipants, $queriesWithFiveParticipants);
    }

    public function testPatrolMemberIsFoundByTieCodeAndListedAmongParticipants(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $event = $this->getAuthorizedEvent($app);
        $section = $this->createProgrammeSection($container, $event, 'api patrol section');
        $programme = $this->createProgramme($app, $event, $section, 'api patrol programme', '2027-06-09 08:00');
        $member = $this->createPatrolMemberOnEvent($app, $event, 'Vlk');
        $this->register($app, $programme, $member);

        $tieResponse = $app->handle($this->createApiRequest(
            '/v3/programme/participant/tie/' . $member->tieCode,
            self::API_KEY,
        ));
        $participantsResponse = $app->handle($this->createApiRequest(
            '/v3/programme/' . $programme->id . '/participants',
            self::API_KEY,
        ));

        self::assertSame(200, $tieResponse->getStatusCode());
        $body = $this->decodeJson($tieResponse);
        self::assertSame(['nickname' => 'Vlk'], $body['participant']);
        self::assertIsArray($body['programmes']);
        self::assertSame([$programme->id], array_column($body['programmes'], 'id'));
        self::assertSame(200, $participantsResponse->getStatusCode());
        self::assertSame(['tieCodes' => [$member->tieCode]], $this->decodeJson($participantsResponse));
    }

    public function testPatrolMemberOfCancelledLeaderIsHidden(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $event = $this->getAuthorizedEvent($app);
        $section = $this->createProgrammeSection($container, $event, 'api cancelled patrol section');
        $programme = $this->createProgramme($app, $event, $section, 'api cancelled patrol', '2027-06-09 10:00');
        $member = $this->createPatrolMemberOnEvent($app, $event, 'Liška');
        $this->register($app, $programme, $member);
        $this->cancel($app, $member);

        $tieResponse = $app->handle($this->createApiRequest(
            '/v3/programme/participant/tie/' . $member->tieCode,
            self::API_KEY,
        ));
        $participantsResponse = $app->handle($this->createApiRequest(
            '/v3/programme/' . $programme->id . '/participants',
            self::API_KEY,
        ));

        self::assertSame(404, $tieResponse->getStatusCode());
        self::assertSame(200, $participantsResponse->getStatusCode());
        self::assertSame(['tieCodes' => []], $this->decodeJson($participantsResponse));
    }

    public function testParticipantByTieCodeReturnsNicknameAndRegistrations(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $event = $this->getAuthorizedEvent($app);
        $section = $this->createProgrammeSection($container, $event, 'api tie section');

        $participant = $this->createPaidIstOnEvent($app, $event, 'Jana');
        $later = $this->createProgramme($app, $event, $section, 'api tie later', '2027-06-04 10:00');
        $earlier = $this->createProgramme($app, $event, $section, 'api tie earlier', '2027-06-04 08:00');
        $removed = $this->createProgramme($app, $event, $section, 'api tie removed', '2027-06-04 09:00');
        $this->register($app, $later, $participant);
        $this->register($app, $earlier, $participant);
        $this->getService($app, ProgrammeRegistrationRepository::class)
            ->softDelete($this->register($app, $removed, $participant));

        $response = $app->handle($this->createApiRequest(
            '/v3/programme/participant/tie/' . strtolower($participant->tieCode),
            self::API_KEY,
        ));

        self::assertSame(200, $response->getStatusCode());
        $body = $this->decodeJson($response);
        self::assertSame(['nickname' => 'Jana'], $body['participant']);
        self::assertIsArray($body['programmes']);
        self::assertSame([$earlier->id, $later->id], array_column($body['programmes'], 'id'));
        $earlierProgramme = $body['programmes'][0];
        self::assertIsArray($earlierProgramme);
        self::assertSame('2027-06-04T08:00:00+02:00', $earlierProgramme['start']);
        self::assertSame($section->id, $earlierProgramme['sectionId']);
    }

    public function testParticipantWithEmptyNicknameIsSentAsNull(): void
    {
        $app = $this->getTestApp();
        $event = $this->getAuthorizedEvent($app);
        $participant = $this->createPaidIstOnEvent($app, $event, '');

        $response = $app->handle($this->createApiRequest(
            '/v3/programme/participant/tie/' . $participant->tieCode,
            self::API_KEY,
        ));

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(['participant' => ['nickname' => null], 'programmes' => []], $this->decodeJson($response));
    }

    public function testParticipantByUnknownTieCodeIs404(): void
    {
        $app = $this->getTestApp();
        $this->getAuthorizedEvent($app);
        $otherEvent = $this->getTypedTestEvent($app, 'default', self::OTHER_EVENT_SLUG);
        $foreignParticipant = $this->createPaidIstOnEvent($app, $otherEvent, 'Foreign');

        $unknown = $app->handle($this->createApiRequest('/v3/programme/participant/tie/unknown-tie-0', self::API_KEY));
        $foreign = $app->handle($this->createApiRequest(
            '/v3/programme/participant/tie/' . $foreignParticipant->tieCode,
            self::API_KEY,
        ));

        self::assertSame(404, $unknown->getStatusCode());
        self::assertSame('', (string)$unknown->getBody());
        self::assertSame(404, $foreign->getStatusCode());
        self::assertSame('', (string)$foreign->getBody());
    }

    public function testCancelledParticipantByTieCodeIs404(): void
    {
        $app = $this->getTestApp();
        $event = $this->getAuthorizedEvent($app);
        $participant = $this->createPaidIstOnEvent($app, $event, 'Cancelled');
        $this->cancel($app, $participant);

        $response = $app->handle($this->createApiRequest(
            '/v3/programme/participant/tie/' . $participant->tieCode,
            self::API_KEY,
        ));

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('', (string)$response->getBody());
    }

    public function testProgrammeParticipantsReturnsTieCodes(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $event = $this->getAuthorizedEvent($app);
        $section = $this->createProgrammeSection($container, $event, 'api participants section');
        $programme = $this->createProgramme($app, $event, $section, 'api participants', '2027-06-05 08:00');

        $first = $this->createPaidIstOnEvent($app, $event, 'First');
        $second = $this->createPaidIstOnEvent($app, $event, 'Second');
        $removed = $this->createPaidIstOnEvent($app, $event, 'Removed');
        $this->register($app, $programme, $second);
        $this->register($app, $programme, $first);
        $this->getService($app, ProgrammeRegistrationRepository::class)
            ->softDelete($this->register($app, $programme, $removed));

        $response = $app->handle($this->createApiRequest(
            '/v3/programme/' . $programme->id . '/participants',
            self::API_KEY,
        ));

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(['tieCodes' => [$first->tieCode, $second->tieCode]], $this->decodeJson($response));
    }

    public function testProgrammeParticipantsExcludesCancelledParticipants(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $event = $this->getAuthorizedEvent($app);
        $section = $this->createProgrammeSection($container, $event, 'api participants cancelled section');
        $programme = $this->createProgramme($app, $event, $section, 'api participants cancelled', '2027-06-05 08:00');

        $active = $this->createPaidIstOnEvent($app, $event, 'Active');
        $cancelled = $this->createPaidIstOnEvent($app, $event, 'Cancelled');
        $this->register($app, $programme, $active);
        $this->register($app, $programme, $cancelled);
        $this->cancel($app, $cancelled);

        $response = $app->handle($this->createApiRequest(
            '/v3/programme/' . $programme->id . '/participants',
            self::API_KEY,
        ));

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(['tieCodes' => [$active->tieCode]], $this->decodeJson($response));
    }

    public function testProgrammeParticipantsOfUnknownOrForeignProgrammeIs404(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $event = $this->getAuthorizedEvent($app);
        $section = $this->createProgrammeSection($container, $event, 'api participants 404 section');
        $deleted = $this->createProgramme($app, $event, $section, 'api participants deleted', '2027-06-05 08:00');
        $deleted->deletedAt = DateTimeUtils::getDateTime();
        $this->getService($app, ProgrammeRepository::class)->persist($deleted);

        $otherEvent = $this->getTypedTestEvent($app, 'default', self::OTHER_EVENT_SLUG);
        $foreign = $this->createProgramme(
            $app,
            $otherEvent,
            $this->createProgrammeSection($container, $otherEvent, 'api participants foreign section'),
            'api participants foreign',
            '2027-06-05 08:00',
        );

        foreach ([$deleted->id, $foreign->id, 999999] as $programmeId) {
            $response = $app->handle($this->createApiRequest(
                '/v3/programme/' . $programmeId . '/participants',
                self::API_KEY,
            ));

            self::assertSame(404, $response->getStatusCode(), (string)$programmeId);
            self::assertSame('', (string)$response->getBody(), (string)$programmeId);
        }
    }

    public function testMissingKeyIs401(): void
    {
        $app = $this->getTestApp();
        $this->getAuthorizedEvent($app);

        $response = $app->handle($this->createRequest('/v3/programme/list'));

        self::assertSame(401, $response->getStatusCode());
        self::assertStringStartsWith('Unauthorized', (string)$response->getBody());
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function getAuthorizedEvent(App $app): Event
    {
        $event = $this->getService($app, EventRepository::class)->findBySlug(self::EVENT_SLUG)
            ?? throw new LogicException('seeded event ' . self::EVENT_SLUG . ' not found');
        $this->mutateEventForTest($app->getContainer(), $event, ['apiKeyEvent' => self::API_KEY]);

        return $event;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createProgramme(
        App $app,
        Event $event,
        ProgrammeSection $section,
        string $name,
        string $start,
        bool $isPreregistered = false,
    ): Programme {
        $programme = new Programme();
        $programme->name = $name;
        $programme->description = 'description of ' . $name . "\nsecond line";
        $programme->place = 'main field';
        $programme->imageFilename = null;
        $programme->start = DateTimeUtils::getDateTime($start);
        $programme->end = DateTimeUtils::getDateTime($start . ' +2 hours');
        $programme->capacity = 10;
        $programme->isPreregistered = $isPreregistered;
        $programme->event = $event;
        $programme->section = $section;
        $programme->setTargetRoles([ParticipantRole::Ist, ParticipantRole::Guest]);
        $this->getService($app, ProgrammeRepository::class)->persist($programme);

        return $programme;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function register(App $app, Programme $programme, Participant $participant): ProgrammeRegistration
    {
        $registration = new ProgrammeRegistration();
        $registration->programme = $programme;
        $registration->participant = $participant;
        $this->getService($app, ProgrammeRegistrationRepository::class)->persist($registration);

        return $registration;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createPaidIstOnEvent(App $app, Event $event, string $nickname): Participant
    {
        $participant = $this->createParticipantWithRole($app->getContainer(), 'ist', $event);
        $participant->nickname = $nickname;
        $this->getService($app, ParticipantRepository::class)->persist($participant);

        $user = $participant->getUserButNotNull();
        $user->status = UserStatus::Paid;
        $this->getService($app, UserRepository::class)->persist($user);

        return $participant;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createPatrolMemberOnEvent(App $app, Event $event, string $nickname): PatrolParticipant
    {
        $leaderParticipant = $this->createParticipantWithRole($app->getContainer(), 'pl', $event);
        $leaderUser = $leaderParticipant->getUserButNotNull();
        $leaderUser->status = UserStatus::Paid;
        $this->getService($app, UserRepository::class)->persist($leaderUser);

        $member = new PatrolParticipant();
        $member->patrolLeader = $this->getService($app, PatrolLeaderRepository::class)->get($leaderParticipant->id);
        $member->firstName = 'Member';
        $member->lastName = 'Of Patrol';
        $member->nickname = $nickname;
        $this->getService($app, PatrolParticipantRepository::class)->persist($member);

        return $member;
    }

    /**
     * @param App<ContainerInterface> $app
     * @return ArrayObject<int, string>
     */
    private function attachQueryCounter(App $app): ArrayObject
    {
        /** @var ArrayObject<int, string> $queryCounter */
        $queryCounter = new ArrayObject();
        $this->getService($app, Connection::class)->onEvent[] = static function (DibiEvent $dibiEvent) use (
            $queryCounter,
        ): void {
            if (($dibiEvent->type & DibiEvent::QUERY) !== 0) {
                $queryCounter->append($dibiEvent->sql);
            }
        };

        return $queryCounter;
    }

    /**
     * @param App<ContainerInterface> $app
     * @param ArrayObject<int, string> $queryCounter
     */
    private function countQueriesOf(App $app, ArrayObject $queryCounter, ServerRequestInterface $request): int
    {
        $queryCountBefore = $queryCounter->count();
        $app->handle($request);

        return $queryCounter->count() - $queryCountBefore;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function cancel(App $app, Participant $participant): void
    {
        $user = $participant->getUserButNotNull();
        $user->status = UserStatus::Cancelled;
        $this->getService($app, UserRepository::class)->persist($user);
    }

    private function createApiRequest(string $path, string $apiKey): ServerRequestInterface
    {
        return $this->createRequest($path)->withHeader('Authorization', 'Bearer ' . $apiKey);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJson(Response $response): array
    {
        $body = json_decode((string)$response->getBody(), true);
        self::assertIsArray($body);

        /** @var array<string, mixed> $body */
        return $body;
    }
}
