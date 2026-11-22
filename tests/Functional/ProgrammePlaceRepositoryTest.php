<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Orm\EntityNotFoundException;
use kissj\Participant\ParticipantRole;
use kissj\Programme\DTO\ProgrammePlaceCountsDTO;
use kissj\Programme\Place\Coordinates;
use kissj\Programme\Place\ProgrammePlace;
use kissj\Programme\Place\ProgrammePlaceRepository;
use kissj\Programme\Place\ProgrammePlaceService;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeRepository;
use kissj\Programme\ProgrammeSection;
use LeanMapper\Connection;
use LeanMapper\IEntityFactory;
use LeanMapper\IMapper;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class ProgrammePlaceRepositoryTest extends AppTestCase
{
    public function testFindForEventIsOrderedByNameAndScopedToEvent(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $this->getTestSlugEvent($eventRepository);
        $placeService = $this->getService($app, ProgrammePlaceService::class);
        $placeRepository = $this->getService($app, ProgrammePlaceRepository::class);

        $zebra = $placeService->findOrCreateByName($event, 'Zebra');
        $alpha = $placeService->findOrCreateByName($event, '  Alpha ');
        $foreign = $placeService->findOrCreateByName($this->getSmallTestEvent($eventRepository), 'Alpha');

        self::assertSame('Alpha', $alpha->name);
        self::assertSame($alpha->id, $placeService->findOrCreateByName($event, 'Alpha')->id);
        self::assertSame(
            [$alpha->id, $zebra->id],
            array_map(static fn (ProgrammePlace $place): int => $place->id, $placeRepository->findForEvent($event)),
        );
        self::assertNull($placeRepository->findForEventById($foreign->id, $event));
        self::assertSame($zebra->id, $placeRepository->getForEventById($zebra->id, $event)->id);

        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage('Programme place not found for event');
        $placeRepository->getForEventById($foreign->id, $event);
    }

    public function testCoordinatesSurviveTheRoundTrip(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $placeRepository = $this->getService($app, ProgrammePlaceRepository::class);
        $place = $this->getService($app, ProgrammePlaceService::class)->findOrCreateByName($event, 'Louka');
        self::assertFalse($place->hasCoordinates());
        self::assertSame('', $place->getCoordinatesInput());

        $place->lat = 50.340847;
        $place->lng = 15.935361;
        $placeRepository->persist($place);

        $reloaded = $placeRepository->getForEventById($place->id, $event);
        self::assertTrue($reloaded->hasCoordinates());
        self::assertSame(50.340847, $reloaded->lat);
        self::assertSame(15.935361, $reloaded->lng);
        self::assertSame('50.340847, 15.935361', $reloaded->getCoordinatesInput());
    }

    public function testCreateStoresCoordinatesAndRefusesTakenName(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $placeService = $this->getService($app, ProgrammePlaceService::class);

        self::assertTrue($placeService->create($event, 'Pole', new Coordinates(50.340847, 15.935361)));
        self::assertFalse($placeService->create($event, 'Pole', null));

        $place = $this->getService($app, ProgrammePlaceRepository::class)->findForEventByName($event, 'Pole');
        self::assertNotNull($place);
        self::assertSame('50.340847, 15.935361', $place->getCoordinatesInput());
    }

    public function testCountsAndDeleteRespectProgrammeReferences(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $section = $this->createProgrammeSection($app->getContainer(), $event, 'Program');
        $placeService = $this->getService($app, ProgrammePlaceService::class);
        $placeRepository = $this->getService($app, ProgrammePlaceRepository::class);
        $referenced = $placeService->findOrCreateByName($event, 'Referenced');
        $empty = $placeService->findOrCreateByName($event, 'Empty');

        $this->persistProgramme($app, $event, $section, $referenced);
        $this->persistProgramme($app, $event, $section, $referenced, deleted: true);

        self::assertSame(2, $placeRepository->countProgrammes($referenced));
        self::assertSame(0, $placeRepository->countProgrammes($empty));
        self::assertEquals(
            [$referenced->id => new ProgrammePlaceCountsDTO(1, 1)],
            $placeRepository->countProgrammesByPlace($event),
        );

        self::assertFalse($placeRepository->deleteUnlessReferenced($referenced));
        self::assertTrue($placeRepository->deleteUnlessReferenced($empty));
        self::assertNotNull($placeRepository->findForEventById($referenced->id, $event));
        self::assertNull($placeRepository->findForEventById($empty->id, $event));
    }

    public function testUpdateRenamesPlaceShownOnProgrammesAndRefusesTakenName(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $section = $this->createProgrammeSection($app->getContainer(), $event, 'Program');
        $placeService = $this->getService($app, ProgrammePlaceService::class);
        $place = $placeService->findOrCreateByName($event, 'Old name');
        $other = $placeService->findOrCreateByName($event, 'Taken');
        $programme = $this->persistProgramme($app, $event, $section, $place);
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);

        self::assertFalse($placeService->update($place, 'Taken', null));
        self::assertSame('Old name', $programmeRepository->getActiveById($programme->id, $event)->getPlaceName());

        self::assertTrue($placeService->update($place, 'Old name', null));
        self::assertTrue($placeService->update($place, 'New name', null));
        self::assertSame('New name', $programmeRepository->getActiveById($programme->id, $event)->getPlaceName());
        self::assertSame(
            'New name',
            $this->getService($app, ProgrammePlaceRepository::class)->getForEventById($place->id, $event)->name,
        );
        self::assertSame('Taken', $other->name);
    }

    public function testUpdateRefusesNameTakenAfterThePreCheck(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $placeService = $this->getService($app, ProgrammePlaceService::class);
        $place = $placeService->findOrCreateByName($event, 'Old name');
        $placeService->update($place, 'Old name', new Coordinates(50.1, 14.2));
        $placeService->findOrCreateByName($event, 'Taken');

        // the pre-check sees no namesake, as when a concurrent request takes the name right after it
        $blindRepository = new /** @table programme_place */ class (
            $this->getService($app, Connection::class),
            $this->getService($app, IMapper::class),
            $this->getService($app, IEntityFactory::class),
        ) extends ProgrammePlaceRepository {
            public function findForEventByName(Event $event, string $name): ?ProgrammePlace
            {
                return null;
            }
        };

        $blindService = new ProgrammePlaceService($blindRepository);
        self::assertFalse($blindService->update($place, 'Taken', new Coordinates(51.5, 15.5)));
        self::assertSame('Old name', $place->name);
        self::assertSame(50.1, $place->lat);
        self::assertSame(14.2, $place->lng);
        $stored = $this->getService($app, ProgrammePlaceRepository::class)->getForEventById($place->id, $event);
        self::assertSame('Old name', $stored->name);
        self::assertSame(50.1, $stored->lat);
        self::assertSame(14.2, $stored->lng);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function persistProgramme(
        App $app,
        Event $event,
        ProgrammeSection $section,
        ProgrammePlace $place,
        bool $deleted = false,
    ): Programme {
        $programme = new Programme();
        $programme->name = 'placed programme';
        $programme->description = 'description';
        $programme->imageFilename = null;
        $programme->start = DateTimeUtils::getDateTime('2027-06-05 10:00');
        $programme->end = DateTimeUtils::getDateTime('2027-06-05 12:00');
        $programme->capacity = 5;
        $programme->isPreregistered = false;
        $programme->event = $event;
        $programme->section = $section;
        $programme->setTargetRoles([ParticipantRole::Ist]);
        if ($deleted) {
            $programme->deletedAt = DateTimeUtils::getDateTime();
        }
        $this->getService($app, ProgrammePlaceService::class)->assignPlace($programme, $place);
        $this->getService($app, ProgrammeRepository::class)->persist($programme);

        return $programme;
    }
}
