<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\ParticipantRole;
use kissj\Programme\Image\ProgrammeImage;
use kissj\Programme\Image\ProgrammeImageRepository;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeRepository;
use kissj\Programme\ProgrammeSection;
use LeanMapper\Connection;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class ProgrammeImageMigrationTest extends AppTestCase
{
    private const string PREVIOUS_MIGRATION_VERSION = '20261122000000';

    public function testMigrationMovesImageFilenamesIntoRowsWithoutTouchingFiles(): void
    {
        $app = $this->getTestApp();
        $connection = $this->getService($app, Connection::class);
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $section = $this->createProgrammeSection($app->getContainer(), $event, 'Program');
        $withHexName = $this->persistProgramme($app, $event, $section);
        $withoutImage = $this->persistProgramme($app, $event, $section);
        $withLegacyName = $this->persistProgramme($app, $event, $section);
        $hexName = str_repeat('0123456789abcdef', 2);

        $this->runPhinx(['command' => 'rollback', '--target' => self::PREVIOUS_MIGRATION_VERSION]);

        $setImageFilename = static fn (Programme $programme, ?string $filename) => $connection->query(
            'UPDATE programme SET image_filename = %s WHERE id = %i',
            $filename,
            $programme->id,
        );
        $setImageFilename($withHexName, $hexName);
        $setImageFilename($withoutImage, null);
        $setImageFilename($withLegacyName, 'legacy.png');

        $this->runPhinx(['command' => 'migrate']);

        $imageRepository = $this->getService($app, ProgrammeImageRepository::class);
        $moved = $imageRepository->findForProgramme($withHexName);
        self::assertCount(1, $moved);
        self::assertSame($hexName, $moved[0]->filename);
        self::assertNull($moved[0]->thumbFilename);
        self::assertSame('', $moved[0]->alt);
        self::assertSame(0, $moved[0]->sortOrder);
        self::assertSame([], $imageRepository->findForProgramme($withoutImage));
        self::assertSame([], $imageRepository->findForProgramme($withLegacyName));
        self::assertFalse($connection->getDatabaseInfo()->getTable('programme')->hasColumn('image_filename'));
    }

    public function testRollbackRestoresTheFirstImageIntoImageFilename(): void
    {
        $app = $this->getTestApp();
        $connection = $this->getService($app, Connection::class);
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $section = $this->createProgrammeSection($app->getContainer(), $event, 'Program');
        $programme = $this->persistProgramme($app, $event, $section);
        $first = str_repeat('a', 32);
        $imageRepository = $this->getService($app, ProgrammeImageRepository::class);
        $this->persistImage($imageRepository, $programme, str_repeat('b', 32), 1);
        $this->persistImage($imageRepository, $programme, $first, 0);

        $this->runPhinx(['command' => 'rollback', '--target' => self::PREVIOUS_MIGRATION_VERSION]);

        self::assertSame(
            $first,
            $connection->fetchSingle('SELECT image_filename FROM programme WHERE id = %i', $programme->id),
        );
        self::assertFalse($connection->getDatabaseInfo()->hasTable('programme_image'));
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function persistProgramme(App $app, Event $event, ProgrammeSection $section): Programme
    {
        $programme = new Programme();
        $programme->name = 'imaged programme';
        $programme->description = 'description';
        $programme->start = DateTimeUtils::getDateTime('2027-06-05 10:00');
        $programme->end = DateTimeUtils::getDateTime('2027-06-05 12:00');
        $programme->capacity = 5;
        $programme->isPreregistered = false;
        $programme->event = $event;
        $programme->section = $section;
        $programme->setTargetRoles([ParticipantRole::Ist]);
        $this->getService($app, ProgrammeRepository::class)->persist($programme);

        return $programme;
    }

    private function persistImage(
        ProgrammeImageRepository $repository,
        Programme $programme,
        string $filename,
        int $sortOrder,
    ): void {
        $image = new ProgrammeImage();
        $image->programme = $programme;
        $image->filename = $filename;
        $image->thumbFilename = null;
        $image->alt = '';
        $image->sortOrder = $sortOrder;
        $repository->persist($image);
    }
}
