<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRole;
use kissj\Programme\Image\ProgrammeImage;
use kissj\Programme\Image\ProgrammeImageRepository;
use kissj\Programme\Place\ProgrammePlace;
use kissj\Programme\Place\ProgrammePlaceRepository;
use kissj\Programme\Place\ProgrammePlaceService;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeRegistration;
use kissj\Programme\ProgrammeRegistrationRepository;
use kissj\Programme\ProgrammeRepository;
use kissj\Programme\ProgrammeSection;
use kissj\Programme\ProgrammeSectionRepository;
use kissj\User\UserRepository;
use kissj\User\UserStatus;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Uri;
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
    private const string PLACES_EVENT_SLUG = 'programme-api-places-event';
    private const string PLACES_EVENT_API_KEY = 'programme-api-places-event-key';

    public function testListIsEmptyForEventWithoutProgrammes(): void
    {
        $app = $this->getTestApp();
        $emptyEvent = $this->getTypedTestEvent($app, 'default', self::EMPTY_EVENT_SLUG);
        $this->mutateEventForTest($app->getContainer(), $emptyEvent, ['apiKeyEvent' => self::EMPTY_EVENT_API_KEY]);

        $response = $app->handle($this->createApiRequest('/v3/programme/list', self::EMPTY_EVENT_API_KEY));

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('Content-Type'));
        self::assertSame(
            '{"sections":[],"places":[],"programmes":[],"registration":{"start":null,"end":null}}',
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
            'placeId' => null,
            'start' => '2027-06-03T09:00:00+02:00',
            'end' => '2027-06-03T11:00:00+02:00',
            'isPreregistered' => true,
            'targetRoles' => ['ist', 'guest'],
            'images' => [],
        ], $body['programmes'][$preregisteredKey]);
    }

    public function testListCarriesEveryPlaceOfTheEventSortedByName(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTypedTestEvent($app, 'default', self::PLACES_EVENT_SLUG);
        $this->mutateEventForTest($app->getContainer(), $event, ['apiKeyEvent' => self::PLACES_EVENT_API_KEY]);
        $placeService = $this->getService($app, ProgrammePlaceService::class);
        $placeRepository = $this->getService($app, ProgrammePlaceRepository::class);

        $beta = $placeService->findOrCreateByName($event, 'beta');
        $beta->lat = 50.340847;
        $beta->lng = 15.935361;
        $placeRepository->persist($beta);
        $alpha = $placeService->findOrCreateByName($event, 'alpha');
        $gamma = $placeService->findOrCreateByName($event, 'gamma');
        $placeService->findOrCreateByName($this->getTypedTestEvent($app, 'default', self::OTHER_EVENT_SLUG), 'aaa');
        $section = $this->createProgrammeSection($app->getContainer(), $event, 'api places section');
        $this->createProgramme($app, $event, $section, 'api places programme', '2027-06-03 09:00', place: $beta);
        $this->createProgramme($app, $event, $section, 'api places other', '2027-06-03 10:00', place: $alpha);

        $request = $this->createApiRequest('/v3/programme/list', self::PLACES_EVENT_API_KEY);

        $body = $this->decodeJson($app->handle($request));

        self::assertSame([
            ['id' => $alpha->id, 'name' => 'alpha', 'lat' => null, 'lng' => null],
            ['id' => $beta->id, 'name' => 'beta', 'lat' => 50.340847, 'lng' => 15.935361],
            ['id' => $gamma->id, 'name' => 'gamma', 'lat' => null, 'lng' => null],
        ], $body['places']);
    }

    public function testProgrammeCarriesPlaceIdAndPlaceName(): void
    {
        $app = $this->getTestApp();
        $event = $this->getAuthorizedEvent($app);
        $beta = $this->getService($app, ProgrammePlaceService::class)->findOrCreateByName($event, 'beta');
        $section = $this->createProgrammeSection($app->getContainer(), $event, 'api place id section');
        $programme = $this->createProgramme($app, $event, $section, 'api place id', '2027-06-03 09:00', place: $beta);

        $listed = $this->findListedProgramme(
            $this->decodeJson($app->handle($this->createApiRequest('/v3/programme/list', self::API_KEY))),
            $programme,
        );

        self::assertSame('beta', $listed['place']);
        self::assertSame($beta->id, $listed['placeId']);
    }

    public function testImageUrlsFollowTheRequestScheme(): void
    {
        $app = $this->getTestApp();
        $event = $this->getAuthorizedEvent($app);
        $section = $this->createProgrammeSection($app->getContainer(), $event, 'api images section');
        $programme = $this->createProgramme($app, $event, $section, 'api images', '2027-06-03 09:00');
        $second = str_repeat('b', 32);
        $first = str_repeat('a', 32);
        $this->createImage($app, $programme, $second, '', 1);
        $this->createImage($app, $programme, $first, 'Stan', 0);
        $request = $this->createApiRequest('/v3/programme/list', self::API_KEY)
            ->withUri(new Uri('https', 'kissj.example', 443, '/v3/programme/list'));

        $listed = $this->findListedProgramme($this->decodeJson($app->handle($request)), $programme);

        self::assertSame([
            [
                'url' => 'https://kissj.example/v3/programme/image/' . $first,
                'thumbUrl' => 'https://kissj.example/v3/programme/image/' . $first . '/thumb',
                'alt' => 'Stan',
            ],
            [
                'url' => 'https://kissj.example/v3/programme/image/' . $second,
                'thumbUrl' => 'https://kissj.example/v3/programme/image/' . $second . '/thumb',
                'alt' => '',
            ],
        ], $listed['images']);
    }

    public function testImageUrlsAreHttpsWhenTheProxyForwardsHttps(): void
    {
        $app = $this->getTestApp();
        $event = $this->getAuthorizedEvent($app);
        $section = $this->createProgrammeSection($app->getContainer(), $event, 'api forwarded section');
        $programme = $this->createProgramme($app, $event, $section, 'api forwarded programme', '2027-06-03 08:00');
        $this->createImage($app, $programme, str_repeat('c', 32), '', 0);
        $request = $this->createApiRequest('/v3/programme/list', self::API_KEY)
            ->withUri(new Uri('http', 'kissj.example', 80, '/v3/programme/list'));

        $forwarded = $this->decodeJson($app->handle($request->withHeader('X-Forwarded-Proto', 'HTTPS, http')));
        $direct = $this->decodeJson($app->handle($request));

        $expectedUrl = 'https://kissj.example/v3/programme/image/' . str_repeat('c', 32);
        self::assertSame(
            [['url' => $expectedUrl, 'thumbUrl' => $expectedUrl . '/thumb', 'alt' => '']],
            $this->findListedProgramme($forwarded, $programme)['images'],
        );
        $plainUrl = 'http://kissj.example/v3/programme/image/' . str_repeat('c', 32);
        self::assertSame(
            [['url' => $plainUrl, 'thumbUrl' => $plainUrl . '/thumb', 'alt' => '']],
            $this->findListedProgramme($direct, $programme)['images'],
        );
    }

    public function testImageUrlsKeepANonStandardPortWhenForwardedAsHttps(): void
    {
        $app = $this->getTestApp();
        $event = $this->getAuthorizedEvent($app);
        $section = $this->createProgrammeSection($app->getContainer(), $event, 'api port section');
        $programme = $this->createProgramme($app, $event, $section, 'api port programme', '2027-06-03 08:00');
        $this->createImage($app, $programme, str_repeat('d', 32), '', 0);
        $request = $this->createApiRequest('/v3/programme/list', self::API_KEY)
            ->withUri(new Uri('http', 'kissj.example', 8080, '/v3/programme/list'))
            ->withHeader('X-Forwarded-Proto', 'https');

        $body = $this->decodeJson($app->handle($request));

        $expectedUrl = 'https://kissj.example:8080/v3/programme/image/' . str_repeat('d', 32);
        self::assertSame(
            [['url' => $expectedUrl, 'thumbUrl' => $expectedUrl . '/thumb', 'alt' => '']],
            $this->findListedProgramme($body, $programme)['images'],
        );
    }

    /**
     * @return array<string, array{string}>
     */
    public static function provideIngressSchemeHeaders(): array
    {
        return [
            'X-Forwarded-Scheme' => ['X-Forwarded-Scheme'],
            'X-Scheme' => ['X-Scheme'],
        ];
    }

    #[DataProvider('provideIngressSchemeHeaders')]
    public function testImageUrlsAreHttpsWhenTheIngressSchemeHeaderSaysHttps(string $headerName): void
    {
        $app = $this->getTestApp();
        $event = $this->getAuthorizedEvent($app);
        $section = $this->createProgrammeSection($app->getContainer(), $event, 'api ingress section');
        $programme = $this->createProgramme($app, $event, $section, 'api ingress programme', '2027-06-03 08:00');
        $this->createImage($app, $programme, str_repeat('e', 32), '', 0);
        $request = $this->createApiRequest('/v3/programme/list', self::API_KEY)
            ->withUri(new Uri('http', 'kissj.example', 80, '/v3/programme/list'))
            ->withHeader($headerName, 'https');

        $body = $this->decodeJson($app->handle($request));

        $expectedUrl = 'https://kissj.example/v3/programme/image/' . str_repeat('e', 32);
        self::assertSame(
            [['url' => $expectedUrl, 'thumbUrl' => $expectedUrl . '/thumb', 'alt' => '']],
            $this->findListedProgramme($body, $programme)['images'],
        );
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
        $placeService = $this->getService($app, ProgrammePlaceService::class);
        $places = [
            $placeService->findOrCreateByName($event, 'api query count first place'),
            $placeService->findOrCreateByName($event, 'api query count second place'),
        ];
        $queryCounter = $this->attachQueryCounter($app);
        $request = $this->createApiRequest('/v3/programme/list', self::QUERY_COUNT_EVENT_API_KEY);

        foreach ([0, 1] as $index) {
            $programme = $this->createProgramme(
                $app,
                $event,
                $sections[$index],
                'api query count ' . $index,
                '2027-06-06 08:00',
                place: $places[$index],
            );
            $this->createImage($app, $programme, md5('api query count ' . $index), '', 0);
        }
        $queriesWithTwoProgrammes = $this->countQueriesOf($app, $queryCounter, $request);

        foreach ([2, 3, 4] as $index) {
            $programme = $this->createProgramme(
                $app,
                $event,
                $sections[$index % 2],
                'api query count ' . $index,
                '2027-06-06 09:00',
                place: $places[$index % 2],
            );
            $this->createImage($app, $programme, md5('api query count ' . $index), '', 0);
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
        ?ProgrammePlace $place = null,
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
        $this->getService($app, ProgrammePlaceService::class)->assignPlace($programme, $place);
        $this->getService($app, ProgrammeRepository::class)->persist($programme);

        return $programme;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createImage(App $app, Programme $programme, string $filename, string $alt, int $sortOrder): void
    {
        $image = new ProgrammeImage();
        $image->programme = $programme;
        $image->filename = $filename;
        $image->thumbFilename = null;
        $image->alt = $alt;
        $image->sortOrder = $sortOrder;
        $this->getService($app, ProgrammeImageRepository::class)->persist($image);
    }

    /**
     * @param array<string, mixed> $body
     * @return array<mixed>
     */
    private function findListedProgramme(array $body, Programme $programme): array
    {
        self::assertIsArray($body['programmes']);
        $key = array_search($programme->id, array_column($body['programmes'], 'id'), true);
        self::assertIsInt($key);
        $listed = $body['programmes'][$key];
        self::assertIsArray($listed);

        return $listed;
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
