<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\ParticipantRole;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeRepository;
use LeanMapper\Connection;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class ProgrammeAutoenrolledMigrationTest extends AppTestCase
{
    private const string PREVIOUS_MIGRATION_VERSION = '20261126000000';

    public function testRollbackAndMigrateKeepAutoenrolledFlag(): void
    {
        $app = $this->getTestApp();
        $connection = $this->getService($app, Connection::class);
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $autoenrolled = $this->persistProgramme($app, $event, true);
        $ordinary = $this->persistProgramme($app, $event, false);

        $this->runPhinx(['command' => 'rollback', '--target' => self::PREVIOUS_MIGRATION_VERSION]);

        self::assertEquals(1, $this->fetchFlag($connection, 'is_preregistered', $autoenrolled->id));
        self::assertEquals(0, $this->fetchFlag($connection, 'is_preregistered', $ordinary->id));

        $this->runPhinx(['command' => 'migrate']);

        self::assertEquals(1, $this->fetchFlag($connection, 'is_autoenrolled', $autoenrolled->id));
        self::assertEquals(0, $this->fetchFlag($connection, 'is_autoenrolled', $ordinary->id));
    }

    private function fetchFlag(Connection $connection, string $column, int $programmeId): mixed
    {
        return $connection->fetchSingle('SELECT %n FROM programme WHERE id = %i', $column, $programmeId);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function persistProgramme(App $app, Event $event, bool $isAutoenrolled): Programme
    {
        $programme = new Programme();
        $programme->name = 'autoenrolled migration programme';
        $programme->description = 'description';
        $programme->start = DateTimeUtils::getDateTime('2027-06-05 10:00');
        $programme->end = DateTimeUtils::getDateTime('2027-06-05 12:00');
        $programme->capacity = 5;
        $programme->isAutoenrolled = $isAutoenrolled;
        $programme->event = $event;
        $programme->section = $this->createProgrammeSection($app->getContainer(), $event, 'Program');
        $programme->setTargetRoles([ParticipantRole::Ist]);
        $this->getService($app, ProgrammeRepository::class)->persist($programme);

        return $programme;
    }
}
