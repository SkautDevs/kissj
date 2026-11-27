<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\EventRepository;
use kissj\Participant\ParticipantRole;
use kissj\Programme\Image\ProgrammeImage;
use kissj\Programme\Image\ProgrammeImageRepository;
use kissj\Programme\Image\ProgrammeImageService;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeRepository;
use Tests\AppTestCase;

class ProgrammeImageServiceTest extends AppTestCase
{
    public function testUpdateAltIsSaved(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->getOneBy(['slug' => 'test-event-slug']);
        $programme = new Programme();
        $programme->name = 'alt programme';
        $programme->description = 'description';
        $programme->start = DateTimeUtils::getDateTime('2027-06-05 10:00');
        $programme->end = DateTimeUtils::getDateTime('2027-06-05 12:00');
        $programme->capacity = 5;
        $programme->isAutoenrolled = false;
        $programme->event = $event;
        $programme->section = $this->createProgrammeSection($app->getContainer(), $event, 'Program');
        $programme->roles = [ParticipantRole::Ist];
        $this->getService($app, ProgrammeRepository::class)->persist($programme);
        $imageRepository = $this->getService($app, ProgrammeImageRepository::class);
        $image = new ProgrammeImage();
        $image->programme = $programme;
        $image->filename = bin2hex(random_bytes(16));
        $image->thumbFilename = null;
        $image->alt = '';
        $image->sortOrder = 0;
        $imageRepository->persist($image);

        $this->getService($app, ProgrammeImageService::class)->updateAlt($image, 'Stan na louce');

        self::assertSame('Stan na louce', $imageRepository->get($image->id)->alt);
    }
}
