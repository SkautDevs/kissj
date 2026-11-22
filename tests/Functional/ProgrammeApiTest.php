<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRole;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeRegistration;
use kissj\Programme\ProgrammeRegistrationRepository;
use kissj\Programme\ProgrammeRepository;
use kissj\Programme\ProgrammeSection;
use kissj\Programme\ProgrammeSectionRepository;
use kissj\User\UserRepository;
use kissj\User\UserStatus;
use LogicException;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
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
        self::assertSame(
            '{"sections":[],"programmes":[],"registration":{"start":null,"end":null}}',
            (string)$response->getBody(),
        );
    }

    public function testListCarriesTheRegistrationWindow(): void
    {
        $app = $this->getTestApp();
        $event = $this->getAuthorizedEvent($app);
        $this->mutateEventForTest($app->getContainer(), $event, [
            'programmeRegistrationStart' => DateTimeUtils::getDateTime('2027-03-15 18:00:00'),
            'programmeRegistrationEnd' => DateTimeUtils::getDateTime('2027-05-19 00:00:00'),
        ]);

        $body = $this->decodeJson($app->handle($this->createApiRequest('/v3/programme/list', self::API_KEY)));

        self::assertIsArray($body['registration']);
        self::assertSame('2027-03-15T18:00:00+01:00', $body['registration']['start']);
        self::assertSame('2027-05-19T00:00:00+02:00', $body['registration']['end']);
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
            'place' => '',
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
        $body = $this->decodeJson($response);
        self::assertArrayNotHasKey('tieCodes', $body);
        $participantIds = $body['participantIds'];
        self::assertIsArray($participantIds);
        self::assertCount(5, $participantIds);
        self::assertContainsOnlyInt($participantIds);
        self::assertSame($queriesWithTwoParticipants, $queriesWithFiveParticipants);
    }

    public function testPatrolMemberIsListedAmongParticipants(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $event = $this->getAuthorizedEvent($app);
        $section = $this->createProgrammeSection($container, $event, 'api patrol section');
        $programme = $this->createProgramme($app, $event, $section, 'api patrol programme', '2027-06-09 08:00');
        $member = $this->createPatrolMemberOnEvent($app, $event, 'Vlk');
        $this->register($app, $programme, $member);

        $participantsResponse = $app->handle($this->createApiRequest(
            '/v3/programme/' . $programme->id . '/participants',
            self::API_KEY,
        ));

        self::assertSame(200, $participantsResponse->getStatusCode());
        self::assertSame(['participantIds' => [$member->id]], $this->decodeJson($participantsResponse));
    }

    public function testPatrolMemberOfCancelledLeaderIsNotListed(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $event = $this->getAuthorizedEvent($app);
        $section = $this->createProgrammeSection($container, $event, 'api cancelled patrol section');
        $programme = $this->createProgramme($app, $event, $section, 'api cancelled patrol', '2027-06-09 10:00');
        $member = $this->createPatrolMemberOnEvent($app, $event, 'Liška');
        $this->register($app, $programme, $member);
        $this->cancel($app, $member);

        $participantsResponse = $app->handle($this->createApiRequest(
            '/v3/programme/' . $programme->id . '/participants',
            self::API_KEY,
        ));

        self::assertSame(200, $participantsResponse->getStatusCode());
        self::assertSame(['participantIds' => []], $this->decodeJson($participantsResponse));
    }

    public function testProgrammeParticipantsReturnsParticipantIds(): void
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
        self::assertSame(['participantIds' => [$first->id, $second->id]], $this->decodeJson($response));
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
        self::assertSame(['participantIds' => [$active->id]], $this->decodeJson($response));
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

    public function testListSendsAHalfSetRegistrationWindowAsItIs(): void
    {
        $app = $this->getTestApp();
        $event = $this->getAuthorizedEvent($app);
        $this->mutateEventForTest($app->getContainer(), $event, [
            'programmeRegistrationStart' => DateTimeUtils::getDateTime('2027-03-15 18:00:00'),
            'programmeRegistrationEnd' => null,
        ]);

        $body = $this->decodeJson($app->handle($this->createApiRequest('/v3/programme/list', self::API_KEY)));

        self::assertSame(['start' => '2027-03-15T18:00:00+01:00', 'end' => null], $body['registration']);
    }

    public function testTheTieReadIsGone(): void
    {
        $app = $this->getTestApp();
        $event = $this->getAuthorizedEvent($app);
        $participant = $this->createPaidIstOnEvent($app, $event, 'Gone');

        // Slim's 404 for a route that does not exist reaches the test as its exception
        $this->expectException(HttpNotFoundException::class);
        $app->handle($this->createApiRequest(
            '/v3/programme/participant/tie/' . $participant->tieCode,
            self::API_KEY,
        ));
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
    private function cancel(App $app, Participant $participant): void
    {
        $user = $participant->getUserButNotNull();
        $user->status = UserStatus::Cancelled;
        $this->getService($app, UserRepository::class)->persist($user);
    }

}
