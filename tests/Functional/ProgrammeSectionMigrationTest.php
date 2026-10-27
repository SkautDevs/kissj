<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\ParticipantRole;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeRepository;
use kissj\Programme\ProgrammeSection;
use kissj\Programme\ProgrammeSectionRepository;
use LeanMapper\Connection;
use Tests\AppTestCase;

class ProgrammeSectionMigrationTest extends AppTestCase
{
    private const string PREVIOUS_MIGRATION_VERSION = '20261025100001';

    public function testRollbackAndMigrateKeepExclusiveGroups(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $connection = $this->getService($app, Connection::class);
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $this->getTestSlugEvent($eventRepository);
        $groupedOnlyEvent = $this->getSmallTestEvent($eventRepository);

        $games = $this->createProgrammeSection($container, $event, 'Hry', true);
        $open = $this->createProgrammeSection($container, $event, 'Open');
        $inGames = $this->persistProgramme($programmeRepository, $event, $games);
        $inOpen = $this->persistProgramme($programmeRepository, $event, $open);

        $this->runPhinx(['command' => 'rollback', '--target' => self::PREVIOUS_MIGRATION_VERSION]);

        $groupOf = static fn (int $programmeId): mixed => $connection->fetchSingle(
            'SELECT exclusive_group_name FROM programme WHERE id = %i',
            $programmeId,
        );
        self::assertSame('Hry', $groupOf($inGames->id));
        self::assertNull($groupOf($inOpen->id));

        $connection->query(
            'INSERT INTO programme (name, description, place, start, "end", capacity, is_preregistered,'
            . ' exclusive_group_name, roles, event_id, created_at, updated_at) VALUES'
            . " ('legacy', 'legacy', 'legacy', '2027-06-05 10:00', '2027-06-05 12:00', 5, 0,"
            . " 'Alpha', ',ist,', %i, '2027-01-01 00:00', '2027-01-01 00:00')",
            $event->id,
        );
        $legacyId = $connection->getInsertId();
        $connection->query(
            'INSERT INTO programme (name, description, place, start, "end", capacity, is_preregistered,'
            . ' exclusive_group_name, roles, event_id, created_at, updated_at) VALUES'
            . " ('grouped', 'grouped', 'grouped', '2027-06-05 10:00', '2027-06-05 12:00', 5, 0,"
            . " 'Solo', ',ist,', %i, '2027-01-01 00:00', '2027-01-01 00:00')",
            $groupedOnlyEvent->id,
        );

        $this->runPhinx(['command' => 'migrate']);

        $sections = $this->getService($app, ProgrammeSectionRepository::class)->findForEvent($event);
        self::assertSame(
            [['Program', false, 0], ['Alpha', true, 1], ['Hry', true, 2]],
            array_map(
                static fn (ProgrammeSection $section): array => [
                    $section->name,
                    $section->isExclusive,
                    $section->sortOrder,
                ],
                $sections,
            ),
        );
        self::assertSame(
            ['Solo'],
            array_map(
                static fn (ProgrammeSection $section): string => $section->name,
                $this->getService($app, ProgrammeSectionRepository::class)->findForEvent($groupedOnlyEvent),
            ),
        );
        $sectionNameOf = static fn (int $programmeId): string => $programmeRepository
            ->getActiveById($programmeId, $event)
            ->section
            ->name;
        self::assertSame('Hry', $sectionNameOf($inGames->id));
        self::assertSame('Program', $sectionNameOf($inOpen->id));
        self::assertSame('Alpha', $sectionNameOf($legacyId));
    }

    private function persistProgramme(
        ProgrammeRepository $programmeRepository,
        Event $event,
        ProgrammeSection $section,
    ): Programme {
        $programme = new Programme();
        $programme->name = 'migrated programme';
        $programme->description = 'description';
        $programme->place = 'somewhere';
        $programme->imageFilename = null;
        $programme->start = DateTimeUtils::getDateTime('2027-06-05 10:00');
        $programme->end = DateTimeUtils::getDateTime('2027-06-05 12:00');
        $programme->capacity = 5;
        $programme->isPreregistered = false;
        $programme->event = $event;
        $programme->section = $section;
        $programme->setTargetRoles([ParticipantRole::Ist]);
        $programmeRepository->persist($programme);

        return $programme;
    }
}
