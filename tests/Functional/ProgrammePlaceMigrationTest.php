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
use kissj\Programme\ProgrammeSection;
use LeanMapper\Connection;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class ProgrammePlaceMigrationTest extends AppTestCase
{
    private const string PREVIOUS_MIGRATION_VERSION = '20261102000000';

    public function testMigrationTurnsDistinctPlaceStringsIntoPlacesPerEvent(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $connection = $this->getService($app, Connection::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $eventA = $this->getTestSlugEvent($eventRepository);
        $eventB = $this->getSmallTestEvent($eventRepository);
        $sectionA = $this->createProgrammeSection($container, $eventA, 'Program');
        $sectionB = $this->createProgrammeSection($container, $eventB, 'Program');

        $this->runPhinx(['command' => 'rollback', '--target' => self::PREVIOUS_MIGRATION_VERSION]);

        $firstInA = $this->insertLegacyProgramme($connection, $eventA, $sectionA, 'Žlutý les');
        $secondInA = $this->insertLegacyProgramme($connection, $eventA, $sectionA, 'Žlutý les');
        $withoutPlace = $this->insertLegacyProgramme($connection, $eventA, $sectionA, '');
        $inB = $this->insertLegacyProgramme($connection, $eventB, $sectionB, 'Žlutý les');

        $this->runPhinx(['command' => 'migrate']);

        $placeRepository = $this->getService($app, ProgrammePlaceRepository::class);
        $placesA = $placeRepository->findForEvent($eventA);
        self::assertSame(
            ['Žlutý les'],
            array_map(static fn (ProgrammePlace $place): string => $place->name, $placesA),
        );
        $placeA = $placesA[0];
        self::assertNull($placeA->lat);
        self::assertNull($placeA->lng);

        $placeIdOf = static fn (int $programmeId): mixed => $connection->fetchSingle(
            'SELECT place_id FROM programme WHERE id = %i',
            $programmeId,
        );
        self::assertSame($placeA->id, $placeIdOf($firstInA));
        self::assertSame($placeA->id, $placeIdOf($secondInA));

        $placesB = $placeRepository->findForEvent($eventB);
        self::assertCount(1, $placesB);
        self::assertSame('Žlutý les', $placesB[0]->name);
        self::assertNotSame($placeA->id, $placesB[0]->id);
        self::assertSame($placesB[0]->id, $placeIdOf($inB));

        self::assertNull($placeIdOf($withoutPlace));
        self::assertFalse($connection->getDatabaseInfo()->getTable('programme')->hasColumn('place'));
    }

    public function testMigrationTrimsLegacyPlaceNames(): void
    {
        $app = $this->getTestApp();
        $connection = $this->getService($app, Connection::class);
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $section = $this->createProgrammeSection($app->getContainer(), $event, 'Program');

        $this->runPhinx(['command' => 'rollback', '--target' => self::PREVIOUS_MIGRATION_VERSION]);

        $trimmed = $this->insertLegacyProgramme($connection, $event, $section, 'Žlutý les');
        $trailingSpace = $this->insertLegacyProgramme($connection, $event, $section, 'Žlutý les ');
        $blank = $this->insertLegacyProgramme($connection, $event, $section, '   ');

        $this->runPhinx(['command' => 'migrate']);

        $places = $this->getService($app, ProgrammePlaceRepository::class)->findForEvent($event);
        self::assertSame(
            ['Žlutý les'],
            array_map(static fn (ProgrammePlace $place): string => $place->name, $places),
        );
        $placeIdOf = static fn (int $programmeId): mixed => $connection->fetchSingle(
            'SELECT place_id FROM programme WHERE id = %i',
            $programmeId,
        );
        self::assertSame($places[0]->id, $placeIdOf($trimmed));
        self::assertSame($places[0]->id, $placeIdOf($trailingSpace));
        self::assertNull($placeIdOf($blank));
    }

    public function testRollbackRestoresPlaceTextFromPlaces(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $connection = $this->getService($app, Connection::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $this->getTestSlugEvent($eventRepository);
        $section = $this->createProgrammeSection($container, $event, 'Program');
        $place = $this->getService($app, ProgrammePlaceService::class)->findOrCreateByName($event, 'Žlutý les');
        $programme = $this->persistProgramme($app, $event, $section, $place);
        $placeless = $this->persistProgramme($app, $event, $section, null);

        $this->runPhinx(['command' => 'rollback', '--target' => self::PREVIOUS_MIGRATION_VERSION]);

        self::assertSame(
            'Žlutý les',
            $connection->fetchSingle('SELECT place FROM programme WHERE id = %i', $programme->id),
        );
        self::assertSame(
            '',
            $connection->fetchSingle('SELECT place FROM programme WHERE id = %i', $placeless->id),
        );
        $databaseInfo = $connection->getDatabaseInfo();
        self::assertFalse($databaseInfo->hasTable('programme_place'));
        self::assertFalse($databaseInfo->getTable('programme')->hasColumn('place_id'));
        $placeColumn = $databaseInfo->getTable('programme')->getColumn('place');
        self::assertFalse($placeColumn->isNullable());
        self::assertNull($placeColumn->getDefault());
    }

    public function testProgrammeResolvesItsPlace(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $this->getTestSlugEvent($eventRepository);
        $section = $this->createProgrammeSection($app->getContainer(), $event, 'Program');
        $place = $this->getService($app, ProgrammePlaceService::class)->findOrCreateByName($event, 'Žlutý les');

        $programme = $this->persistProgramme($app, $event, $section, $place);

        $reloaded = $this->getService($app, ProgrammeRepository::class)->getActiveById($programme->id, $event);
        self::assertNotNull($reloaded->programmePlace);
        self::assertSame($place->id, $reloaded->programmePlace->id);
        self::assertSame('Žlutý les', $reloaded->getPlaceName());

        $placeless = $this->persistProgramme($app, $event, $section, null);
        $reloadedPlaceless = $this->getService($app, ProgrammeRepository::class)->getActiveById($placeless->id, $event);
        self::assertSame('', $reloadedPlaceless->getPlaceName());
    }

    private function insertLegacyProgramme(
        Connection $connection,
        Event $event,
        ProgrammeSection $section,
        string $place,
    ): int {
        $connection->query(
            'INSERT INTO programme (name, description, place, start, "end", capacity, is_preregistered,'
            . ' section_id, roles, event_id, created_at, updated_at) VALUES'
            . " ('legacy', 'legacy', %s, '2027-06-05 10:00', '2027-06-05 12:00', 5, 0,"
            . " %i, ',ist,', %i, '2027-01-01 00:00', '2027-01-01 00:00')",
            $place,
            $section->id,
            $event->id,
        );

        return $connection->getInsertId();
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function persistProgramme(
        App $app,
        Event $event,
        ProgrammeSection $section,
        ?ProgrammePlace $place,
    ): Programme {
        $programme = new Programme();
        $programme->name = 'placed programme';
        $programme->description = 'description';
        $programme->start = DateTimeUtils::getDateTime('2027-06-05 10:00');
        $programme->end = DateTimeUtils::getDateTime('2027-06-05 12:00');
        $programme->capacity = 5;
        $programme->isAutoenrolled = false;
        $programme->event = $event;
        $programme->section = $section;
        $programme->roles = [ParticipantRole::Ist];
        $this->getService($app, ProgrammePlaceService::class)->assignPlace($programme, $place);
        $this->getService($app, ProgrammeRepository::class)->persist($programme);

        return $programme;
    }
}
