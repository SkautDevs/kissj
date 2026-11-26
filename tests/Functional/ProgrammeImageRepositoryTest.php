<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Orm\EntityNotFoundException;
use kissj\Participant\ParticipantRole;
use kissj\Programme\Image\ProgrammeImage;
use kissj\Programme\Image\ProgrammeImageRepository;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeRepository;
use kissj\Programme\ProgrammeSection;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class ProgrammeImageRepositoryTest extends AppTestCase
{
    public function testImagesAreGroupedByProgrammeInSortOrder(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $section = $this->createProgrammeSection($app->getContainer(), $event, 'Program');
        $first = $this->persistProgramme($app, $event, $section);
        $second = $this->persistProgramme($app, $event, $section);
        $withoutImages = $this->persistProgramme($app, $event, $section);
        $repository = $this->getService($app, ProgrammeImageRepository::class);
        $firstLater = $this->persistImage($repository, $first, 'a', 1);
        $secondOnly = $this->persistImage($repository, $second, 'b', 0);
        $firstEarlier = $this->persistImage($repository, $first, 'c', 0);

        $grouped = $repository->findForProgrammes([$first, $second, $withoutImages]);

        self::assertSame(
            [$first->id => [$firstEarlier->id, $firstLater->id], $second->id => [$secondOnly->id]],
            array_map(static fn (array $images): array => array_map(
                static fn (ProgrammeImage $image): int => $image->id,
                $images,
            ), $grouped),
        );
        self::assertSame([], $repository->findForProgrammes([]));
        self::assertSame($firstEarlier->id, $repository->findFirstForProgramme($first)?->id);
        self::assertNull($repository->findFirstForProgramme($withoutImages));
        self::assertSame(2, $repository->getNextSortOrder($first));
        self::assertSame(0, $repository->getNextSortOrder($withoutImages));
    }

    public function testGetForProgrammeByIdRefusesAnImageOfAnotherProgramme(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $section = $this->createProgrammeSection($app->getContainer(), $event, 'Program');
        $owner = $this->persistProgramme($app, $event, $section);
        $other = $this->persistProgramme($app, $event, $section);
        $repository = $this->getService($app, ProgrammeImageRepository::class);
        $image = $this->persistImage($repository, $owner, 'a', 0);

        self::assertSame($image->id, $repository->getForProgrammeById($image->id, $owner)->id);

        $this->expectException(EntityNotFoundException::class);
        $repository->getForProgrammeById($image->id, $other);
    }

    public function testServableImageNeedsALiveProgrammeOfAnApiEvent(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $apiEvent = $this->getTestSlugEvent($eventRepository);
        $this->mutateEventForTest($app->getContainer(), $apiEvent, ['apiKeyEvent' => 'image-test-key']);
        $plainEvent = $this->getSmallTestEvent($eventRepository);
        $apiSection = $this->createProgrammeSection($app->getContainer(), $apiEvent, 'Program');
        $plainSection = $this->createProgrammeSection($app->getContainer(), $plainEvent, 'Program');
        $live = $this->persistProgramme($app, $apiEvent, $apiSection);
        $deleted = $this->persistProgramme($app, $apiEvent, $apiSection);
        $deleted->deletedAt = DateTimeUtils::getDateTime();
        $this->getService($app, ProgrammeRepository::class)->persist($deleted);
        $ofPlainEvent = $this->persistProgramme($app, $plainEvent, $plainSection);
        $repository = $this->getService($app, ProgrammeImageRepository::class);
        $sharedFirst = $this->persistImage($repository, $live, 'shared', 0);
        $this->persistImage($repository, $live, 'shared', 1);
        $this->persistImage($repository, $deleted, 'deleted', 0);
        $this->persistImage($repository, $ofPlainEvent, 'plain', 0);

        self::assertSame($sharedFirst->id, $repository->findServableByFilename($this->hexName('shared'))?->id);
        self::assertSame(2, $repository->countByFilename($this->hexName('shared')));
        self::assertNull($repository->findServableByFilename($this->hexName('deleted')));
        self::assertNull($repository->findServableByFilename($this->hexName('plain')));
        self::assertNull($repository->findServableByFilename($this->hexName('missing')));
    }

    public function testFillThumbIfEmptyKeepsTheFirstThumb(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $section = $this->createProgrammeSection($app->getContainer(), $event, 'Program');
        $programme = $this->persistProgramme($app, $event, $section);
        $repository = $this->getService($app, ProgrammeImageRepository::class);
        $image = $this->persistImage($repository, $programme, 'a', 0);

        self::assertTrue($repository->fillThumbIfEmpty($image, $this->hexName('first-thumb')));
        self::assertFalse($repository->fillThumbIfEmpty($image, $this->hexName('second-thumb')));
        self::assertSame($this->hexName('first-thumb'), $repository->get($image->id)->thumbFilename);
    }

    private function hexName(string $seed): string
    {
        return md5($seed);
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
        $programme->isAutoenrolled = false;
        $programme->event = $event;
        $programme->section = $section;
        $programme->roles = [ParticipantRole::Ist];
        $this->getService($app, ProgrammeRepository::class)->persist($programme);

        return $programme;
    }

    private function persistImage(
        ProgrammeImageRepository $repository,
        Programme $programme,
        string $seed,
        int $sortOrder,
    ): ProgrammeImage {
        $image = new ProgrammeImage();
        $image->programme = $programme;
        $image->filename = $this->hexName($seed);
        $image->thumbFilename = null;
        $image->alt = '';
        $image->sortOrder = $sortOrder;
        $repository->persist($image);

        return $image;
    }
}
