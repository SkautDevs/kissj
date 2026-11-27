<?php

declare(strict_types=1);

namespace Tests\Functional;

use Dibi\UniqueConstraintViolationException;
use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\ParticipantRole;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeEnrolmentService;
use kissj\Programme\ProgrammeRepository;
use LeanMapper\Connection;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class ProgrammeEnrolmentMigrationTest extends AppTestCase
{
    private const string PREVIOUS_MIGRATION_VERSION = '20261122000001';

    public function testRollbackAndMigrateKeepEnrolmentsAndWindow(): void
    {
        $app = $this->getTestApp();
        $connection = $this->getService($app, Connection::class);
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $this->openProgrammeEnrolmentWindow($app, $event->slug);
        $active = $this->persistProgramme($app, $event, '2027-06-05 10:00');
        $withdrawn = $this->persistProgramme($app, $event, '2027-06-06 10:00');
        $ist = $this->createPaidIst($app->getContainer(), 'Migrated', 'Enrolment');
        self::assertNull($service->enrol($ist, $active->id)->error);
        self::assertNull($service->enrol($ist, $withdrawn->id)->error);
        self::assertNull($service->withdraw($ist, $withdrawn->id)->error);

        $rowsBefore = $this->fetchDeletedAtOfParticipant($connection, 'programme_enrolment', $ist->id);
        $startBefore = $this->fetchEventColumn($connection, 'programme_enrolment_start', $event->id);
        $endBefore = $this->fetchEventColumn($connection, 'programme_enrolment_end', $event->id);
        self::assertCount(2, $rowsBefore);
        self::assertNull(array_values($rowsBefore)[0]);
        self::assertNotNull(array_values($rowsBefore)[1]);
        self::assertNotNull($startBefore);
        self::assertNotNull($endBefore);

        $this->runPhinx(['command' => 'rollback', '--target' => self::PREVIOUS_MIGRATION_VERSION]);

        self::assertEquals(
            $rowsBefore,
            $this->fetchDeletedAtOfParticipant($connection, 'programme_registration', $ist->id),
        );
        self::assertEquals(
            $startBefore,
            $this->fetchEventColumn($connection, 'programme_registration_start', $event->id),
        );
        self::assertEquals($endBefore, $this->fetchEventColumn($connection, 'programme_registration_end', $event->id));
        self::assertSame(
            [
                'programme_registration_active_uidx',
                'programme_registration_participant_idx',
                'programme_registration_programme_idx',
            ],
            $this->fetchIndexNames($connection, 'programme_registration'),
        );

        $this->runPhinx(['command' => 'migrate']);

        self::assertEquals(
            $rowsBefore,
            $this->fetchDeletedAtOfParticipant($connection, 'programme_enrolment', $ist->id),
        );
        self::assertEquals($startBefore, $this->fetchEventColumn($connection, 'programme_enrolment_start', $event->id));
        self::assertEquals($endBefore, $this->fetchEventColumn($connection, 'programme_enrolment_end', $event->id));
        self::assertSame(
            [
                'programme_enrolment_active_uidx',
                'programme_enrolment_participant_idx',
                'programme_enrolment_programme_idx',
            ],
            $this->fetchIndexNames($connection, 'programme_enrolment'),
        );
        self::assertSame(
            0,
            $connection->fetchSingle(
                "SELECT COUNT(*) FROM sqlite_master WHERE type = 'index' AND name LIKE %s",
                'programme_registration%',
            ),
        );

        $this->expectException(UniqueConstraintViolationException::class);
        $connection->query(
            'INSERT INTO programme_enrolment (programme_id, participant_id, created_at, updated_at)'
            . " VALUES (%i, %i, '2027-01-01 00:00', '2027-01-01 00:00')",
            $active->id,
            $ist->id,
        );
    }

    public function testParticipantDeletionStillCascadesToEnrolmentsAfterRollbackAndMigrate(): void
    {
        $app = $this->getTestApp();
        $connection = $this->getService($app, Connection::class);
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $this->openProgrammeEnrolmentWindow($app, $event->slug);
        $programme = $this->persistProgramme($app, $event, '2027-06-05 10:00');
        $ist = $this->createPaidIst($app->getContainer(), 'Cascaded', 'Enrolment');
        self::assertNull($service->enrol($ist, $programme->id)->error);

        $this->runPhinx(['command' => 'rollback', '--target' => self::PREVIOUS_MIGRATION_VERSION]);
        $this->runPhinx(['command' => 'migrate']);

        $connection->query('DELETE FROM participant WHERE id = %i', $ist->id);

        self::assertSame([], $this->fetchDeletedAtOfParticipant($connection, 'programme_enrolment', $ist->id));
    }

    /**
     * @return array<mixed>
     */
    private function fetchDeletedAtOfParticipant(Connection $connection, string $table, int $participantId): array
    {
        return $connection->fetchPairs(
            'SELECT id, deleted_at FROM %n WHERE participant_id = %i ORDER BY id',
            $table,
            $participantId,
        );
    }

    private function fetchEventColumn(Connection $connection, string $column, int $eventId): mixed
    {
        return $connection->fetchSingle('SELECT %n FROM event WHERE id = %i', $column, $eventId);
    }

    /**
     * @return array<mixed>
     */
    private function fetchIndexNames(Connection $connection, string $table): array
    {
        return $connection->fetchPairs(
            "SELECT name FROM sqlite_master WHERE type = 'index' AND tbl_name = %s AND sql IS NOT NULL ORDER BY name",
            $table,
        );
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function persistProgramme(App $app, Event $event, string $start): Programme
    {
        $programme = new Programme();
        $programme->name = 'enrolment migration programme';
        $programme->description = 'description';
        $programme->start = DateTimeUtils::getDateTime($start);
        $programme->end = DateTimeUtils::getDateTime($start . ' +2 hours');
        $programme->capacity = 5;
        $programme->isAutoenrolled = false;
        $programme->event = $event;
        $programme->section = $this->createProgrammeSection($app->getContainer(), $event, 'Program');
        $programme->roles = [ParticipantRole::Ist];
        $this->getService($app, ProgrammeRepository::class)->persist($programme);

        return $programme;
    }
}
