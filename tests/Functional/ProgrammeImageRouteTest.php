<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\FileHandler\SaveFileHandler;
use kissj\Participant\ParticipantRole;
use kissj\Programme\Image\ProgrammeImage;
use kissj\Programme\Image\ProgrammeImageProcessor;
use kissj\Programme\Image\ProgrammeImageRepository;
use kissj\Programme\Image\ProgrammeImageService;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeRepository;
use Monolog\Level;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Tests\AppTestCase;
use Tests\Support\ImageFixtures;

class ProgrammeImageRouteTest extends AppTestCase
{
    private const string EVENT_SLUG = 'test-event-slug';
    private const string API_KEY = 'programme-image-route-key';
    private const string UNKNOWN_FILENAME = '0123456789abcdef0123456789abcdef';
    private const string UPLOAD_FOLDER = __DIR__ . '/../../uploads/';

    /** @var list<ProgrammeImage> */
    private array $storedImages = [];

    public function testFullImageIsServedPubliclyWithImmutableHeaders(): void
    {
        $app = $this->getTestApp();
        try {
            $processed = $this->getService($app, ProgrammeImageProcessor::class)
                ->process(ImageFixtures::png(400, 300));
            $image = $this->storeImage($app, $this->createApiProgramme($app), $processed->full->bytes);

            $response = $this->get($app, '/v3/programme/image/' . $image->filename);

            self::assertSame(200, $response->getStatusCode());
            self::assertSame('image/webp', $response->getHeaderLine('Content-Type'));
            self::assertSame('public, max-age=31536000, immutable', $response->getHeaderLine('Cache-Control'));
            self::assertSame('*', $response->getHeaderLine('Access-Control-Allow-Origin'));
            self::assertSame('nosniff', $response->getHeaderLine('X-Content-Type-Options'));
            self::assertFalse($response->hasHeader('Access-Control-Allow-Credentials'));
            self::assertFalse($response->hasHeader('Set-Cookie'));
            self::assertFalse($response->hasHeader('Pragma'));
            self::assertFalse($response->hasHeader('Expires'));
            self::assertSame($processed->full->bytes, (string)$response->getBody());
        } finally {
            $this->removeStoredFiles($app);
        }
    }

    public function testImageRequestStartsNoSession(): void
    {
        $originalRequestUri = $_SERVER['REQUEST_URI'] ?? null;
        $path = '/v3/programme/image/' . self::UNKNOWN_FILENAME;
        $_SERVER['REQUEST_URI'] = $path;
        try {
            // the policy reads only the URI, so a filename without a row is enough
            $response = $this->get($this->getTestApp(), $path);

            self::assertSame(404, $response->getStatusCode());
            self::assertSame(PHP_SESSION_NONE, session_status());
        } finally {
            if ($originalRequestUri === null) {
                unset($_SERVER['REQUEST_URI']);
            } else {
                $_SERVER['REQUEST_URI'] = $originalRequestUri;
            }
        }
    }

    public function testUnknownFilenameIs404(): void
    {
        $app = $this->getTestApp();
        $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);

        $response = $this->get($app, '/v3/programme/image/' . self::UNKNOWN_FILENAME);

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('', (string)$response->getBody());
    }

    public function testImageOfEventWithoutProgrammeApiIs404(): void
    {
        $app = $this->getTestApp();
        try {
            $event = $this->getSmallTestEvent($this->getService($app, EventRepository::class));
            $image = $this->storeImage($app, $this->createProgramme($app, $event), ImageFixtures::png(40, 30));

            self::assertSame(404, $this->get($app, '/v3/programme/image/' . $image->filename)->getStatusCode());
        } finally {
            $this->removeStoredFiles($app);
        }
    }

    public function testImageOfDeletedProgrammeIs404(): void
    {
        $app = $this->getTestApp();
        try {
            $programme = $this->createApiProgramme($app);
            $image = $this->storeImage($app, $programme, ImageFixtures::png(40, 30));
            $programme->deletedAt = DateTimeUtils::getDateTime();
            $this->getService($app, ProgrammeRepository::class)->persist($programme);

            $path = '/v3/programme/image/' . $image->filename;
            self::assertSame(404, $this->get($app, $path)->getStatusCode());
            self::assertSame(404, $this->get($app, $path . '/thumb')->getStatusCode());
        } finally {
            $this->removeStoredFiles($app);
        }
    }

    public function testNonHexFilenameIsNotRouted(): void
    {
        $app = $this->getTestApp();

        // Slim's 404 for a route that does not exist reaches the test as its exception
        $this->expectException(HttpNotFoundException::class);
        $this->get($app, '/v3/programme/image/' . str_repeat('g', 32));
    }

    public function testLazyThumbIsGeneratedOnceAndStored(): void
    {
        $app = $this->getTestApp();
        try {
            $image = $this->storeImage($app, $this->createApiProgramme($app), ImageFixtures::png(1200, 800));
            $repository = $this->getService($app, ProgrammeImageRepository::class);

            $first = $this->get($app, '/v3/programme/image/' . $image->filename . '/thumb');

            self::assertSame(200, $first->getStatusCode());
            self::assertSame('image/webp', $first->getHeaderLine('Content-Type'));
            self::assertSame('public, max-age=31536000, immutable', $first->getHeaderLine('Cache-Control'));
            $firstBody = (string)$first->getBody();
            $size = getimagesizefromstring($firstBody);
            self::assertNotFalse($size);
            self::assertSame([256, 256], [$size[0], $size[1]]);

            $thumbFilename = $repository->get($image->id)->thumbFilename;
            self::assertIsString($thumbFilename);
            self::assertMatchesRegularExpression('/^[a-f0-9]{32}$/D', $thumbFilename);
            self::assertFileExists(self::UPLOAD_FOLDER . $thumbFilename);

            $second = $this->get($app, '/v3/programme/image/' . $image->filename . '/thumb');

            self::assertSame(200, $second->getStatusCode());
            self::assertSame($firstBody, (string)$second->getBody());
            self::assertSame($thumbFilename, $repository->get($image->id)->thumbFilename);
        } finally {
            $this->removeStoredFiles($app);
        }
    }

    public function testStoredThumbIsServed(): void
    {
        $app = $this->getTestApp();
        try {
            $processed = $this->getService($app, ProgrammeImageProcessor::class)
                ->process(ImageFixtures::png(400, 300));
            $image = $this->storeImage(
                $app,
                $this->createApiProgramme($app),
                $processed->full->bytes,
                $processed->thumb->bytes,
            );

            $response = $this->get($app, '/v3/programme/image/' . $image->filename . '/thumb');

            self::assertSame(200, $response->getStatusCode());
            self::assertSame('image/webp', $response->getHeaderLine('Content-Type'));
            self::assertSame('public, max-age=31536000, immutable', $response->getHeaderLine('Cache-Control'));
            self::assertSame('*', $response->getHeaderLine('Access-Control-Allow-Origin'));
            self::assertSame($processed->thumb->bytes, (string)$response->getBody());
        } finally {
            $this->removeStoredFiles($app);
        }
    }

    public function testThumbOfOriginalAboveTwentyMegapixelsIs404(): void
    {
        $app = $this->getTestApp();
        try {
            $original = ImageFixtures::pngHeaderOnly(5000, 4001);
            $image = $this->storeImage($app, $this->createApiProgramme($app), $original);

            // a decode attempt would fail on the header-only bytes and log a warning, which the test does not allow
            $thumb = $this->get($app, '/v3/programme/image/' . $image->filename . '/thumb');

            self::assertSame(404, $thumb->getStatusCode());
            self::assertSame('public, max-age=3600', $thumb->getHeaderLine('Cache-Control'));
            self::assertNull($this->getService($app, ProgrammeImageRepository::class)->get($image->id)->thumbFilename);

            $full = $this->get($app, '/v3/programme/image/' . $image->filename);

            self::assertSame(200, $full->getStatusCode());
            self::assertSame($original, (string)$full->getBody());
        } finally {
            $this->removeStoredFiles($app);
        }
    }

    public function testThumbOfUndecodableOriginalIs404(): void
    {
        $app = $this->getTestApp();
        try {
            $image = $this->storeImage($app, $this->createApiProgramme($app), ImageFixtures::pngHeaderOnly(100, 100));
            $this->expectLog(Level::Warning, 'Programme image file unreadable');

            $thumb = $this->get($app, '/v3/programme/image/' . $image->filename . '/thumb');

            self::assertSame(404, $thumb->getStatusCode());
            self::assertNull($this->getService($app, ProgrammeImageRepository::class)->get($image->id)->thumbFilename);
            $records = $this->getTestLogHandler()->getRecords();
            self::assertCount(1, $records);
            self::assertSame($image->id, $records[0]->context['programmeImageId'] ?? null);
            self::assertArrayNotHasKey('filename', $records[0]->context);
            self::assertSame(200, $this->get($app, '/v3/programme/image/' . $image->filename)->getStatusCode());
        } finally {
            $this->removeStoredFiles($app);
        }
    }

    public function testMissingFileIs404(): void
    {
        $app = $this->getTestApp();
        $image = $this->persistImage($app, $this->createApiProgramme($app), self::UNKNOWN_FILENAME, null);
        $this->expectLog(Level::Warning, 'Programme image file unreadable');

        self::assertSame(404, $this->get($app, '/v3/programme/image/' . $image->filename)->getStatusCode());
        self::assertSame(404, $this->get($app, '/v3/programme/image/' . $image->filename . '/thumb')->getStatusCode());
    }

    public function testThumbOfARequestThatWonTheRaceIsServed(): void
    {
        $app = $this->getTestApp();
        try {
            $processed = $this->getService($app, ProgrammeImageProcessor::class)
                ->process(ImageFixtures::png(400, 300));
            $stale = $this->storeImage($app, $this->createApiProgramme($app), $processed->full->bytes);
            $fileHandler = $this->getService($app, SaveFileHandler::class);
            $repository = $this->getService($app, ProgrammeImageRepository::class);
            $winnerThumbFilename = $fileHandler->saveContents($processed->thumb->bytes, $processed->thumb->contentType);
            self::assertTrue($repository->fillThumbIfEmpty($repository->get($stale->id), $winnerThumbFilename));
            $fileCountBefore = $this->countUploadedFiles();

            $file = $this->getService($app, ProgrammeImageService::class)->openThumb($stale);

            self::assertNotNull($file);
            self::assertSame($processed->thumb->bytes, $file->stream->getContents());
            self::assertSame($fileCountBefore, $this->countUploadedFiles(), 'the losing thumb file was not deleted');
            self::assertSame($winnerThumbFilename, $repository->get($stale->id)->thumbFilename);
        } finally {
            $this->removeStoredFiles($app);
        }
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function get(App $app, string $path): ResponseInterface
    {
        return $app->handle($this->createRequest($path));
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createApiProgramme(App $app): Programme
    {
        return $this->createProgramme($app, $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY));
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createProgramme(App $app, Event $event): Programme
    {
        $programme = new Programme();
        $programme->name = 'imaged programme';
        $programme->description = 'description';
        $programme->start = DateTimeUtils::getDateTime('2027-06-05 10:00');
        $programme->end = DateTimeUtils::getDateTime('2027-06-05 12:00');
        $programme->capacity = 5;
        $programme->isPreregistered = false;
        $programme->event = $event;
        $programme->section = $this->createProgrammeSection($app->getContainer(), $event, 'Program');
        $programme->setTargetRoles([ParticipantRole::Ist]);
        $this->getService($app, ProgrammeRepository::class)->persist($programme);

        return $programme;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function storeImage(
        App $app,
        Programme $programme,
        string $fullBytes,
        ?string $thumbBytes = null,
    ): ProgrammeImage {
        $fileHandler = $this->getService($app, SaveFileHandler::class);
        $filename = $fileHandler->saveContents($fullBytes, 'image/webp');
        $thumbFilename = $thumbBytes === null ? null : $fileHandler->saveContents($thumbBytes, 'image/webp');

        $image = $this->persistImage($app, $programme, $filename, $thumbFilename);
        $this->storedImages[] = $image;

        return $image;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function persistImage(
        App $app,
        Programme $programme,
        string $filename,
        ?string $thumbFilename,
    ): ProgrammeImage {
        $image = new ProgrammeImage();
        $image->programme = $programme;
        $image->filename = $filename;
        $image->thumbFilename = $thumbFilename;
        $image->alt = '';
        $image->sortOrder = 0;
        $this->getService($app, ProgrammeImageRepository::class)->persist($image);

        return $image;
    }

    /**
     * Removes the stored files with the thumbs the requests generated, read back from the rows.
     *
     * @param App<ContainerInterface> $app
     */
    private function removeStoredFiles(App $app): void
    {
        $fileHandler = $this->getService($app, SaveFileHandler::class);
        $repository = $this->getService($app, ProgrammeImageRepository::class);
        foreach ($this->storedImages as $image) {
            $fileHandler->deleteFile($image->filename);
            $thumbFilename = $repository->findOneBy(['id' => $image->id])->thumbFilename ?? $image->thumbFilename;
            if ($thumbFilename !== null) {
                $fileHandler->deleteFile($thumbFilename);
            }
        }
        $this->storedImages = [];
    }

    private function countUploadedFiles(): int
    {
        $files = glob(self::UPLOAD_FOLDER . '*');
        if ($files === false) {
            throw new RuntimeException('glob function fails');
        }

        return count($files);
    }
}
