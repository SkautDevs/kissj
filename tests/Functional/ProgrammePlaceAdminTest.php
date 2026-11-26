<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\ParticipantRole;
use kissj\Programme\Place\ProgrammePlace;
use kissj\Programme\Place\ProgrammePlaceRepository;
use kissj\Programme\Place\ProgrammePlaceService;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeRepository;
use kissj\User\LoginToken;
use kissj\User\LoginTokenRepository;
use kissj\User\UserRepository;
use kissj\User\UserRole;
use kissj\User\UserService;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class ProgrammePlaceAdminTest extends AppTestCase
{
    private const string PLACES_URL = '/v2/event/test-event-slug/admin/programmes/places';

    public function testAdminCreatesPlaceWithCoordinatesCopiedFromGoogleMaps(): void
    {
        $app = $this->createLoggedAdmin('place-admin-create@example.com');

        $response = $app->handle($this->createRequest(
            self::PLACES_URL,
            'POST',
            ['name' => '  Žlutý les ', 'coordinates' => '50.3408472, 15.9353613'],
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame([$this->translate($app, 'flash.success.programmePlaceSaved')], $this->flashed($app));
        $place = $this->findPlaceByName($app, 'Žlutý les');
        self::assertNotNull($place);
        self::assertNotNull($place->lat);
        self::assertNotNull($place->lng);
        self::assertEqualsWithDelta(50.340847, $place->lat, 0.0000005);
        self::assertEqualsWithDelta(15.935361, $place->lng, 0.0000005);
    }

    public function testEmptyCoordinatesAreAllowedAndClearExistingOnes(): void
    {
        $app = $this->createLoggedAdmin('place-admin-clear@example.com');
        $app->handle($this->createRequest(
            self::PLACES_URL,
            'POST',
            ['name' => 'Louka', 'coordinates' => '50.3408472, 15.9353613'],
        ));
        $place = $this->findPlaceByName($app, 'Louka');
        self::assertNotNull($place);
        self::assertTrue($place->hasCoordinates());

        $response = $app->handle($this->createRequest(
            self::PLACES_URL . '/' . $place->id . '/edit',
            'POST',
            ['name' => 'Louka', 'coordinates' => ''],
        ));

        self::assertSame(302, $response->getStatusCode());
        $reloaded = $this->reloadPlace($app, $place);
        self::assertNull($reloaded->lat);
        self::assertNull($reloaded->lng);
    }

    public function testInvalidCoordinatesAreRejectedAndNothingSaved(): void
    {
        $app = $this->createLoggedAdmin('place-admin-invalid@example.com');
        $place = $this->createPlace($app, $this->getEvent($app), 'Stará louka');

        $responseCreate = $app->handle($this->createRequest(
            self::PLACES_URL,
            'POST',
            ['name' => 'Žlutý les', 'coordinates' => '50,34 15,93'],
        ));
        $responseEdit = $app->handle($this->createRequest(
            self::PLACES_URL . '/' . $place->id . '/edit',
            'POST',
            ['name' => 'Nová louka', 'coordinates' => 'north of the lake'],
        ));

        self::assertSame(302, $responseCreate->getStatusCode());
        self::assertSame(302, $responseEdit->getStatusCode());
        self::assertSame(
            array_fill(0, 2, $this->translate($app, 'flash.error.programmePlaceInvalidCoordinates')),
            $this->flashed($app),
        );
        self::assertNull($this->findPlaceByName($app, 'Žlutý les'));
        self::assertSame('Stará louka', $this->reloadPlace($app, $place)->name);
    }

    public function testBlankNameIsRefusedOnCreateAndEdit(): void
    {
        $app = $this->createLoggedAdmin('place-admin-blank@example.com');
        $event = $this->getEvent($app);
        $place = $this->createPlace($app, $event, 'Keeps its name');

        $app->handle($this->createRequest(self::PLACES_URL, 'POST', ['name' => '   ', 'coordinates' => '']));
        $app->handle($this->createRequest(
            self::PLACES_URL . '/' . $place->id . '/edit',
            'POST',
            ['name' => '', 'coordinates' => '50.3408472, 15.9353613'],
        ));

        self::assertSame(
            array_fill(0, 2, $this->translate($app, 'flash.error.programmePlaceNameRequired')),
            $this->flashed($app),
        );
        self::assertCount(1, $this->getService($app, ProgrammePlaceRepository::class)->findForEvent($event));
        $reloaded = $this->reloadPlace($app, $place);
        self::assertSame('Keeps its name', $reloaded->name);
        self::assertFalse($reloaded->hasCoordinates());
    }

    public function testDuplicateNameIsRejected(): void
    {
        $app = $this->createLoggedAdmin('place-admin-duplicate@example.com');
        $event = $this->getEvent($app);
        $existing = $this->createPlace($app, $event, 'Žlutý les');
        $other = $this->createPlace($app, $event, 'Modrý les');
        $otherEvent = $this->getSmallTestEvent($this->getService($app, EventRepository::class));
        $this->createPlace($app, $otherEvent, 'Zelený les');

        $app->handle($this->createRequest(
            self::PLACES_URL,
            'POST',
            ['name' => 'Žlutý les', 'coordinates' => ''],
        ));
        $app->handle($this->createRequest(
            self::PLACES_URL . '/' . $other->id . '/edit',
            'POST',
            ['name' => 'Žlutý les', 'coordinates' => ''],
        ));
        $app->handle($this->createRequest(
            self::PLACES_URL,
            'POST',
            ['name' => 'Zelený les', 'coordinates' => ''],
        ));

        self::assertSame([
            $this->translate($app, 'flash.error.programmePlaceNameTaken'),
            $this->translate($app, 'flash.error.programmePlaceNameTaken'),
            $this->translate($app, 'flash.success.programmePlaceSaved'),
        ], $this->flashed($app));
        $names = array_map(
            static fn (ProgrammePlace $place): string => $place->name,
            $this->getService($app, ProgrammePlaceRepository::class)->findForEvent($event),
        );
        self::assertSame(['Modrý les', 'Zelený les', 'Žlutý les'], $names);
        self::assertSame($existing->id, $this->findPlaceByName($app, 'Žlutý les')?->id);
    }

    public function testRenameShowsOnProgrammes(): void
    {
        $app = $this->createLoggedAdmin('place-admin-rename@example.com');
        $event = $this->getEvent($app);
        $place = $this->createPlace($app, $event, 'Žlutý les');
        $programme = $this->createProgramme($app, $event, $place, false);

        $response = $app->handle($this->createRequest(
            self::PLACES_URL . '/' . $place->id . '/edit',
            'POST',
            ['name' => 'Modrý les', 'coordinates' => ''],
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame([$this->translate($app, 'flash.success.programmePlaceSaved')], $this->flashed($app));
        $reloaded = $this->getService($app, ProgrammeRepository::class)->getActiveById($programme->id, $event);
        self::assertSame('Modrý les', $reloaded->getPlaceName());
    }

    public function testDeleteUnusedPlace(): void
    {
        $app = $this->createLoggedAdmin('place-admin-delete@example.com');
        $place = $this->createPlace($app, $this->getEvent($app), 'Unused');

        $response = $app->handle($this->createRequest(self::PLACES_URL . '/' . $place->id . '/delete', 'POST'));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame([$this->translate($app, 'flash.success.programmePlaceDeleted')], $this->flashed($app));
        self::assertNull($this->getService($app, ProgrammePlaceRepository::class)->findOneBy(['id' => $place->id]));
    }

    public function testDeleteOfUsedPlaceIsRefused(): void
    {
        $app = $this->createLoggedAdmin('place-admin-delete-used@example.com');
        $event = $this->getEvent($app);
        $place = $this->createPlace($app, $event, 'Used by a deleted programme');
        $this->createProgramme($app, $event, $place, true);

        $response = $app->handle($this->createRequest(self::PLACES_URL . '/' . $place->id . '/delete', 'POST'));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame(
            [str_replace('%count%', '1', $this->translate($app, 'flash.error.programmePlaceHasProgrammes'))],
            $this->flashed($app),
        );
        self::assertNotNull($this->getService($app, ProgrammePlaceRepository::class)->findOneBy(['id' => $place->id]));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function foreignPlaceRoutes(): array
    {
        return [
            'edit' => ['/edit'],
            'delete' => ['/delete'],
        ];
    }

    #[DataProvider('foreignPlaceRoutes')]
    public function testPlaceOfAnotherEventIsNotFound(string $routeSuffix): void
    {
        $app = $this->createLoggedAdmin('place-admin-foreign@example.com');
        $otherEvent = $this->getSmallTestEvent($this->getService($app, EventRepository::class));
        $foreign = $this->createPlace($app, $otherEvent, 'Foreign place');

        $response = $app->handle($this->createRequest(
            self::PLACES_URL . '/' . $foreign->id . $routeSuffix,
            'POST',
            ['name' => 'hijacked', 'coordinates' => ''],
        ));

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('Foreign place', $this->reloadPlace($app, $foreign)->name);
    }

    public function testOverviewListsPlacesWithCoordinatesAndCounts(): void
    {
        $app = $this->createLoggedAdmin('place-admin-overview@example.com');
        $event = $this->getEvent($app);
        $counted = $this->createPlace($app, $event, 'Counted place');
        $counted->lat = 50.340847;
        $counted->lng = 15.935361;
        $this->getService($app, ProgrammePlaceRepository::class)->persist($counted);
        $this->createProgramme($app, $event, $counted, false);
        $this->createProgramme($app, $event, $counted, true);
        $this->createProgramme($app, $event, $counted, true);
        $this->createPlace($app, $event, 'Active only place');
        $this->createProgramme($app, $event, $this->findPlaceByName($app, 'Active only place'), false);
        $deletedLabel = str_replace('%count%', '2', $this->translate($app, 'programme.admin.places.deletedCount'));

        $response = $app->handle($this->createRequest(self::PLACES_URL));

        self::assertSame(200, $response->getStatusCode());
        $body = (string)$response->getBody();
        self::assertStringContainsString('value="50.340847, 15.935361"', $body);
        self::assertSame(1, preg_match('~value="Counted place".*?<b>(\d+)</b>([^<]*)<~s', $body, $matches));
        self::assertSame('1', $matches[1]);
        self::assertSame($deletedLabel, trim($matches[2]));
        self::assertSame(1, preg_match('~value="Active only place".*?<b>(\d+)</b>([^<]*)<~s', $body, $matches));
        self::assertSame('1', $matches[1]);
        self::assertSame('', trim($matches[2]));
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function getEvent(App $app): Event
    {
        return $this->getService($app, EventRepository::class)->getOneBy(['slug' => 'test-event-slug']);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createPlace(App $app, Event $event, string $name): ProgrammePlace
    {
        return $this->getService($app, ProgrammePlaceService::class)->findOrCreateByName($event, $name);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function findPlaceByName(App $app, string $name): ?ProgrammePlace
    {
        return $this->getService($app, ProgrammePlaceRepository::class)
            ->findForEventByName($this->getEvent($app), $name);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function reloadPlace(App $app, ProgrammePlace $place): ProgrammePlace
    {
        return $this->getService($app, ProgrammePlaceRepository::class)->get($place->id);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createProgramme(App $app, Event $event, ?ProgrammePlace $place, bool $softDeleted): Programme
    {
        $programme = new Programme();
        $programme->name = 'place programme';
        $programme->description = 'description';
        $programme->start = DateTimeUtils::getDateTime('2027-06-05 10:00');
        $programme->end = DateTimeUtils::getDateTime('2027-06-05 12:00');
        $programme->capacity = 5;
        $programme->isAutoenrolled = false;
        $programme->event = $event;
        $programme->section = $this->createProgrammeSection($app->getContainer(), $event, 'Program');
        $this->getService($app, ProgrammePlaceService::class)->assignPlace($programme, $place);
        if ($softDeleted) {
            $programme->deletedAt = DateTimeUtils::getDateTime('2027-01-01 10:00');
        }
        $programme->roles = [ParticipantRole::Ist];
        $this->getService($app, ProgrammeRepository::class)->persist($programme);

        return $programme;
    }

    /**
     * @return App<ContainerInterface>
     */
    private function createLoggedAdmin(string $email): App
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->getOneBy(['slug' => 'test-event-slug']);
        $user = $this->getService($app, UserService::class)->registerEmailUser($email, $event);
        $user->role = UserRole::Admin;
        $this->getService($app, UserRepository::class)->persist($user);

        $loginToken = new LoginToken();
        $loginToken->token = bin2hex(random_bytes(16));
        $loginToken->user = $user;
        $loginToken->used = false;
        $this->getService($app, LoginTokenRepository::class)->persist($loginToken);

        $response = $app->handle($this->createRequest('/v2/event/test-event-slug/tryLogin/' . $loginToken->token));
        self::assertSame(302, $response->getStatusCode());

        return $this->getTestApp(false);
    }
}
