<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\FileHandler\SaveFileHandler;
use kissj\Participant\ParticipantRole;
use kissj\Programme\Image\ProgrammeImage;
use kissj\Programme\Image\ProgrammeImageRepository;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeRepository;
use kissj\User\LoginToken;
use kissj\User\LoginTokenRepository;
use kissj\User\UserRepository;
use kissj\User\UserRole;
use kissj\User\UserService;
use LogicException;
use Monolog\Level;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Psr7\UploadedFile;
use Tests\AppTestCase;
use Tests\Support\ImageFixtures;

class ProgrammeImageAdminTest extends AppTestCase
{
    private const string EVENT_SLUG = 'test-event-slug';
    private const string PROGRAMMES_URL = '/v2/event/test-event-slug/admin/programmes';
    private const string UPLOAD_FOLDER = __DIR__ . '/../../uploads/';

    /** @var list<string> */
    private array $temporaryPaths = [];

    /** @var list<string> */
    private array $storedFilenames = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryPaths as $path) {
            @unlink($path);
        }
        $this->temporaryPaths = [];

        parent::tearDown();
    }

    public function testCreateWithTwoUploadsStoresTwoWebpImagesInOrder(): void
    {
        $app = $this->createLoggedAdmin('image-admin-create@example.com');
        $name = 'imaged create ' . bin2hex(random_bytes(4));

        try {
            $response = $app->handle($this->createProgrammeRequest($app, $name, [
                $this->upload(ImageFixtures::jpeg(2000, 1500), 'photo.jpg', 'image/jpeg'),
                $this->upload(ImageFixtures::png(400, 300), 'plan.png', 'image/png'),
            ]));

            self::assertSame(302, $response->getStatusCode());
            $images = $this->imagesOf($app, $this->getProgrammeByName($app, $name));
            self::assertCount(2, $images);
            self::assertSame([0, 1], [$images[0]->sortOrder, $images[1]->sortOrder]);
            self::assertNotNull($images[0]->thumbFilename);
            self::assertNotNull($images[1]->thumbFilename);

            $full = getimagesizefromstring($this->readStored($app, $images[0]->filename));
            self::assertNotFalse($full);
            self::assertSame([1600, 1200, IMAGETYPE_WEBP], [$full[0], $full[1], $full[2]]);
            $thumb = getimagesizefromstring($this->readStored($app, $images[0]->thumbFilename));
            self::assertNotFalse($thumb);
            self::assertSame([256, 256], [$thumb[0], $thumb[1]]);
            $secondFull = getimagesizefromstring($this->readStored($app, $images[1]->filename));
            self::assertNotFalse($secondFull);
            self::assertSame([400, 300], [$secondFull[0], $secondFull[1]]);
        } finally {
            $this->removeStoredFiles($app);
        }
    }

    public function testUploadAboveTwentyMegapixelsIsRefusedWithFlashAndOthersKept(): void
    {
        $app = $this->createLoggedAdmin('image-admin-huge@example.com');
        $name = 'imaged huge ' . bin2hex(random_bytes(4));

        try {
            $response = $app->handle($this->createProgrammeRequest($app, $name, [
                $this->upload(ImageFixtures::pngHeaderOnly(5000, 4001), 'huge.png', 'image/png'),
                $this->upload(ImageFixtures::jpeg(800, 600), 'photo.jpg', 'image/jpeg'),
            ]));

            self::assertSame(302, $response->getStatusCode());
            $images = $this->imagesOf($app, $this->getProgrammeByName($app, $name));
            self::assertCount(1, $images);
            $full = getimagesizefromstring($this->readStored($app, $images[0]->filename));
            self::assertNotFalse($full);
            self::assertSame([800, 600], [$full[0], $full[1]]);
            self::assertContains(
                str_replace(
                    '%name%',
                    'huge.png',
                    $this->translate($app, 'flash.warning.programmeImageTooManyPixels'),
                ),
                $this->flashed($app),
            );
        } finally {
            $this->removeStoredFiles($app);
        }
    }

    public function testUndecodableUploadIsRefusedWithFlashAndOthersKept(): void
    {
        $app = $this->createLoggedAdmin('image-admin-undecodable@example.com');
        $name = 'imaged undecodable ' . bin2hex(random_bytes(4));

        try {
            // the header passes the content sniff, but GD cannot decode the missing pixel data
            $response = $app->handle($this->createProgrammeRequest($app, $name, [
                $this->upload(ImageFixtures::pngHeaderOnly(100, 100), 'broken.png', 'image/png'),
                $this->upload(ImageFixtures::jpeg(800, 600), 'photo.jpg', 'image/jpeg'),
            ]));

            self::assertSame(302, $response->getStatusCode());
            $images = $this->imagesOf($app, $this->getProgrammeByName($app, $name));
            self::assertCount(1, $images);
            $full = getimagesizefromstring($this->readStored($app, $images[0]->filename));
            self::assertNotFalse($full);
            self::assertSame([800, 600], [$full[0], $full[1]]);
            $flashed = $this->flashed($app);
            self::assertContains($this->translate($app, 'flash.warning.invalidFileType'), $flashed);
            self::assertContains($this->translate($app, 'flash.success.programmeSaved'), $flashed);
        } finally {
            $this->removeStoredFiles($app);
        }
    }

    public function testUpdateAppendsUploadsAfterExistingImages(): void
    {
        $app = $this->createLoggedAdmin('image-admin-update@example.com');
        $programme = $this->createProgramme($app, $this->getEvent($app));
        $existing = $this->persistImage($app, $programme, bin2hex(random_bytes(16)), 0);

        try {
            $request = $this->createRequest(
                self::PROGRAMMES_URL . '/' . $programme->id . '/edit',
                'POST',
                $this->programmeBody($app, 'imaged update'),
            )->withUploadedFiles(['images' => [$this->upload(ImageFixtures::png(40, 30), 'a.png', 'image/png')]]);
            $response = $app->handle($request);

            self::assertSame(302, $response->getStatusCode());
            $images = $this->imagesOf($app, $programme);
            self::assertCount(2, $images);
            self::assertSame($existing->id, $images[0]->id);
            self::assertSame(1, $images[1]->sortOrder);
        } finally {
            $this->removeStoredFiles($app);
        }
    }

    public function testFileOverUploadMaxFilesizeIsDroppedWithFlashAndOthersKept(): void
    {
        $app = $this->createLoggedAdmin('image-admin-ini-size@example.com');
        $name = 'imaged ini size ' . bin2hex(random_bytes(4));
        $tooBig = $this->upload('', 'too-big.jpg', 'image/jpeg', UPLOAD_ERR_INI_SIZE);

        try {
            $response = $app->handle($this->createProgrammeRequest($app, $name, [
                $tooBig,
                $this->upload(ImageFixtures::jpeg(800, 600), 'photo.jpg', 'image/jpeg'),
            ]));

            self::assertSame(302, $response->getStatusCode());
            self::assertCount(1, $this->imagesOf($app, $this->getProgrammeByName($app, $name)));
            $flashed = $this->flashed($app);
            self::assertContains($this->translate($app, 'flash.warning.fileTooBig'), $flashed);
            self::assertContains($this->translate($app, 'flash.success.programmeSaved'), $flashed);
        } finally {
            $this->removeStoredFiles($app);
        }
    }

    public function testCreateWithBodyDroppedOverPostMaxSizeRedirectsToFormWithFlash(): void
    {
        $app = $this->createLoggedAdmin('image-admin-post-max-create@example.com');
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        $programmeCount = count($programmeRepository->findActiveForEvent($this->getEvent($app)));

        $response = $app->handle($this->createOversizedRequest(self::PROGRAMMES_URL . '/new'));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame(self::PROGRAMMES_URL . '/new', $response->getHeaderLine('Location'));
        self::assertSame([$this->expectedTooLargeFlash($app)], $this->flashed($app));
        self::assertCount($programmeCount, $programmeRepository->findActiveForEvent($this->getEvent($app)));
    }

    public function testUpdateWithBodyDroppedOverPostMaxSizeRedirectsToFormWithFlash(): void
    {
        $app = $this->createLoggedAdmin('image-admin-post-max-update@example.com');
        $programme = $this->createProgramme($app, $this->getEvent($app));

        $response = $app->handle($this->createOversizedRequest(
            self::PROGRAMMES_URL . '/' . $programme->id . '/edit',
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame(self::PROGRAMMES_URL . '/' . $programme->id . '/edit', $response->getHeaderLine('Location'));
        self::assertSame([$this->expectedTooLargeFlash($app)], $this->flashed($app));
        $reloaded = $this->getService($app, ProgrammeRepository::class)->get($programme->id);
        self::assertSame('image admin programme', $reloaded->name);
    }

    public function testFullImageServesFirstImageWithSecurityHeaders(): void
    {
        $app = $this->createLoggedAdmin('image-admin-full@example.com');
        $programme = $this->createProgramme($app, $this->getEvent($app));
        $png = ImageFixtures::png(40, 30);
        $fileHandler = $this->getService($app, SaveFileHandler::class);
        $first = $this->rememberStored($fileHandler->saveContents($png, 'image/png'));
        $second = $this->rememberStored($fileHandler->saveContents(ImageFixtures::png(20, 20), 'image/png'));
        $this->persistImage($app, $programme, $second, 1);
        $this->persistImage($app, $programme, $first, 0);

        try {
            $response = $app->handle($this->createRequest(self::PROGRAMMES_URL . '/' . $programme->id . '/image'));

            self::assertSame(200, $response->getStatusCode());
            self::assertSame('image/png', $response->getHeaderLine('Content-Type'));
            self::assertSame('nosniff', $response->getHeaderLine('X-Content-Type-Options'));
            self::assertStringContainsString("script-src 'none'", $response->getHeaderLine('Content-Security-Policy'));
            self::assertSame($png, (string)$response->getBody());
        } finally {
            $this->removeStoredFiles($app);
        }
    }

    public function testFullImageOfProgrammeWithoutImageIs404(): void
    {
        $app = $this->createLoggedAdmin('image-admin-full-none@example.com');
        $programme = $this->createProgramme($app, $this->getEvent($app));

        $response = $app->handle($this->createRequest(self::PROGRAMMES_URL . '/' . $programme->id . '/image'));

        self::assertSame(404, $response->getStatusCode());
    }

    public function testFullImageOfMissingProgrammeIs404(): void
    {
        $app = $this->createLoggedAdmin('image-admin-full-missing@example.com');

        $response = $app->handle($this->createRequest(self::PROGRAMMES_URL . '/999999/image'));

        self::assertSame(404, $response->getStatusCode());
    }

    public function testStoredFilenameNotMadeByTheAppIsNotOpened(): void
    {
        $app = $this->createLoggedAdmin('image-admin-filename-guard@example.com');
        $programme = $this->createProgramme($app, $this->getEvent($app));
        // the file exists next to the upload folder, so only the name check stands between it and the response
        self::assertFileExists(self::UPLOAD_FOLDER . '../composer.json');
        $image = $this->persistImage($app, $programme, '../composer.json', 0);
        $this->expectLog(Level::Warning, 'Programme image file unreadable');

        $full = $app->handle($this->createRequest(self::PROGRAMMES_URL . '/' . $programme->id . '/image'));
        $thumb = $app->handle($this->createRequest(
            self::PROGRAMMES_URL . '/' . $programme->id . '/images/' . $image->id . '/thumb',
        ));

        self::assertSame(404, $full->getStatusCode());
        self::assertSame('', (string)$full->getBody());
        self::assertSame(404, $thumb->getStatusCode());
    }

    public function testEditFormHasMultipleFileInputAndResizeScript(): void
    {
        $app = $this->createLoggedAdmin('image-admin-form@example.com');
        $programme = $this->createProgramme($app, $this->getEvent($app));

        $response = $app->handle($this->createRequest(self::PROGRAMMES_URL . '/' . $programme->id . '/edit'));
        $body = (string)$response->getBody();

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('name="images[]"', $body);
        self::assertStringContainsString(' multiple', $body);
        self::assertStringContainsString('accept="image/jpeg,image/png,image/gif"', $body);
        self::assertStringContainsString('data-resize-max-edge="1600"', $body);
        self::assertStringContainsString('data-resize-quality="0.9"', $body);
        self::assertStringContainsString('/js/programme-image-resize.js', $body);
        self::assertStringNotContainsString('name="image"', $body);
        self::assertStringContainsString($this->translate($app, 'programme.admin.images.none'), $body);
    }

    public function testEditPageListsImagesWithAltMoveAndDeleteForms(): void
    {
        $app = $this->createLoggedAdmin('image-admin-list@example.com');
        $programme = $this->createProgramme($app, $this->getEvent($app));
        $first = $this->persistImage($app, $programme, bin2hex(random_bytes(16)), 0, 'first <alt>');
        $second = $this->persistImage($app, $programme, bin2hex(random_bytes(16)), 1);

        $response = $app->handle($this->createRequest(self::PROGRAMMES_URL . '/' . $programme->id . '/edit'));
        $body = (string)$response->getBody();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(2, substr_count($body, 'name="alt"'));
        self::assertStringContainsString('value="first &lt;alt&gt;"', $body);
        foreach ([$first, $second] as $image) {
            $imageUrl = self::PROGRAMMES_URL . '/' . $programme->id . '/images/' . $image->id;
            self::assertStringContainsString('src="' . $imageUrl . '/thumb"', $body);
            foreach (['/edit', '/move/up', '/move/down', '/delete'] as $action) {
                self::assertStringContainsString('action="' . $imageUrl . $action . '"', $body);
            }
        }
    }

    public function testAltTextIsSaved(): void
    {
        $app = $this->createLoggedAdmin('image-admin-alt@example.com');
        $programme = $this->createProgramme($app, $this->getEvent($app));
        $image = $this->persistImage($app, $programme, bin2hex(random_bytes(16)), 0);

        $response = $app->handle($this->createRequest(
            self::PROGRAMMES_URL . '/' . $programme->id . '/images/' . $image->id . '/edit',
            'POST',
            ['alt' => '  Stan na louce  '],
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame(self::PROGRAMMES_URL . '/' . $programme->id . '/edit', $response->getHeaderLine('Location'));
        $reloaded = $this->getService($app, ProgrammeImageRepository::class)->get($image->id);
        self::assertSame('Stan na louce', $reloaded->alt);
        self::assertSame([$this->translate($app, 'flash.success.programmeImageSaved')], $this->flashed($app));
    }

    public function testMoveSwapsOrder(): void
    {
        $app = $this->createLoggedAdmin('image-admin-move@example.com');
        $programme = $this->createProgramme($app, $this->getEvent($app));
        $first = $this->persistImage($app, $programme, bin2hex(random_bytes(16)), 0);
        $second = $this->persistImage($app, $programme, bin2hex(random_bytes(16)), 1);

        $response = $app->handle($this->createRequest(
            self::PROGRAMMES_URL . '/' . $programme->id . '/images/' . $second->id . '/move/up',
            'POST',
        ));

        self::assertSame(302, $response->getStatusCode());
        $images = $this->imagesOf($app, $programme);
        self::assertSame([$second->id, $first->id], [$images[0]->id, $images[1]->id]);
        self::assertSame([0, 1], [$images[0]->sortOrder, $images[1]->sortOrder]);

        $app->handle($this->createRequest(
            self::PROGRAMMES_URL . '/' . $programme->id . '/images/' . $second->id . '/move/up',
            'POST',
        ));
        $unchanged = $this->imagesOf($app, $programme);
        self::assertSame([$second->id, $first->id], [$unchanged[0]->id, $unchanged[1]->id]);
    }

    public function testDeleteRemovesRowAndBothFiles(): void
    {
        $app = $this->createLoggedAdmin('image-admin-delete@example.com');
        $programme = $this->createProgramme($app, $this->getEvent($app));
        $fileHandler = $this->getService($app, SaveFileHandler::class);
        $filename = $this->rememberStored($fileHandler->saveContents(ImageFixtures::png(40, 30), 'image/png'));
        $thumbFilename = $this->rememberStored($fileHandler->saveContents(ImageFixtures::png(20, 20), 'image/png'));
        $image = $this->persistImage($app, $programme, $filename, 0, '', $thumbFilename);

        try {
            $response = $app->handle($this->createRequest(
                self::PROGRAMMES_URL . '/' . $programme->id . '/images/' . $image->id . '/delete',
                'POST',
            ));

            self::assertSame(302, $response->getStatusCode());
            self::assertNull($this->getService($app, ProgrammeImageRepository::class)->findOneBy(['id' => $image->id]));
            self::assertFileDoesNotExist(self::UPLOAD_FOLDER . $filename);
            self::assertFileDoesNotExist(self::UPLOAD_FOLDER . $thumbFilename);
            self::assertSame([$this->translate($app, 'flash.success.programmeImageDeleted')], $this->flashed($app));
        } finally {
            $this->removeStoredFiles($app);
        }
    }

    public function testDeleteKeepsFileStillReferencedByAnotherImage(): void
    {
        $app = $this->createLoggedAdmin('image-admin-delete-shared@example.com');
        $event = $this->getEvent($app);
        $programme = $this->createProgramme($app, $event);
        $otherProgramme = $this->createProgramme($app, $event);
        $png = ImageFixtures::png(40, 30);
        $fileHandler = $this->getService($app, SaveFileHandler::class);
        $filename = $this->rememberStored($fileHandler->saveContents($png, 'image/png'));
        $image = $this->persistImage($app, $programme, $filename, 0);
        $this->persistImage($app, $otherProgramme, $filename, 0);

        try {
            $app->handle($this->createRequest(
                self::PROGRAMMES_URL . '/' . $programme->id . '/images/' . $image->id . '/delete',
                'POST',
            ));

            self::assertFileExists(self::UPLOAD_FOLDER . $filename);
            $served = $app->handle($this->createRequest(self::PROGRAMMES_URL . '/' . $otherProgramme->id . '/image'));
            self::assertSame(200, $served->getStatusCode());
            self::assertSame($png, (string)$served->getBody());
        } finally {
            $this->removeStoredFiles($app);
        }
    }

    public function testDeleteSkipsStoredFilenameNotMadeByTheApp(): void
    {
        $app = $this->createLoggedAdmin('image-admin-delete-guard@example.com');
        $programme = $this->createProgramme($app, $this->getEvent($app));
        // the file exists next to the upload folder, so only the name check keeps it from being deleted
        self::assertFileExists(self::UPLOAD_FOLDER . '../composer.json');
        $image = $this->persistImage($app, $programme, '../composer.json', 0);
        $this->expectLog(Level::Warning, 'Programme image file not deleted, unexpected filename');

        $response = $app->handle($this->createRequest(
            self::PROGRAMMES_URL . '/' . $programme->id . '/images/' . $image->id . '/delete',
            'POST',
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertNull($this->getService($app, ProgrammeImageRepository::class)->findOneBy(['id' => $image->id]));
        self::assertFileExists(self::UPLOAD_FOLDER . '../composer.json');
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function foreignImageRoutes(): array
    {
        return [
            'edit' => ['/edit', 'POST'],
            'move' => ['/move/up', 'POST'],
            'delete' => ['/delete', 'POST'],
            'thumb' => ['/thumb', 'GET'],
        ];
    }

    #[DataProvider('foreignImageRoutes')]
    public function testImageOfAnotherProgrammeIsNotFound(string $suffix, string $method): void
    {
        $app = $this->createLoggedAdmin('image-admin-foreign@example.com');
        $event = $this->getEvent($app);
        $programme = $this->createProgramme($app, $event);
        $foreignProgramme = $this->createProgramme($app, $event);
        // real files, so that a route without the programme check would answer 200 instead of 404
        $fileHandler = $this->getService($app, SaveFileHandler::class);
        $filename = $this->rememberStored($fileHandler->saveContents(ImageFixtures::png(40, 30), 'image/png'));
        $thumbFilename = $this->rememberStored($fileHandler->saveContents(ImageFixtures::png(20, 20), 'image/png'));
        $foreignImage = $this->persistImage($app, $foreignProgramme, $filename, 0, 'kept', $thumbFilename);

        try {
            $response = $app->handle($this->createRequest(
                self::PROGRAMMES_URL . '/' . $programme->id . '/images/' . $foreignImage->id . $suffix,
                $method,
                $method === 'POST' ? ['alt' => 'hijacked'] : [],
            ));

            self::assertSame(404, $response->getStatusCode());
            $reloaded = $this->getService($app, ProgrammeImageRepository::class)
                ->findOneBy(['id' => $foreignImage->id]);
            self::assertNotNull($reloaded);
            self::assertSame('kept', $reloaded->alt);
            self::assertSame(0, $reloaded->sortOrder);
            self::assertFileExists(self::UPLOAD_FOLDER . $filename);
            self::assertFileExists(self::UPLOAD_FOLDER . $thumbFilename);
        } finally {
            $this->removeStoredFiles($app);
        }
    }

    public function testAdminThumbRouteWorksForEventWithoutProgrammeApi(): void
    {
        $app = $this->createLoggedAdmin('image-admin-thumb@example.com');
        $event = $this->getEvent($app);
        self::assertNull($event->apiKeyEvent);
        $programme = $this->createProgramme($app, $event);
        $filename = $this->rememberStored(
            $this->getService($app, SaveFileHandler::class)->saveContents(ImageFixtures::png(1200, 800), 'image/png'),
        );
        $image = $this->persistImage($app, $programme, $filename, 0);

        try {
            $response = $app->handle($this->createRequest(
                self::PROGRAMMES_URL . '/' . $programme->id . '/images/' . $image->id . '/thumb',
            ));

            self::assertSame(200, $response->getStatusCode());
            self::assertSame('nosniff', $response->getHeaderLine('X-Content-Type-Options'));
            $size = getimagesizefromstring((string)$response->getBody());
            self::assertNotFalse($size);
            self::assertSame([256, 256], [$size[0], $size[1]]);
        } finally {
            // remembers the thumb the request generated
            $this->imagesOf($app, $programme);
            $this->removeStoredFiles($app);
        }
    }

    /**
     * @param App<ContainerInterface> $app
     * @param list<UploadedFile> $uploads
     */
    private function createProgrammeRequest(App $app, string $name, array $uploads): ServerRequestInterface
    {
        return $this->createRequest(self::PROGRAMMES_URL . '/new', 'POST', $this->programmeBody($app, $name))
            ->withUploadedFiles(['images' => $uploads]);
    }

    /**
     * @param App<ContainerInterface> $app
     * @return array<string, list<string>|string>
     */
    private function programmeBody(App $app, string $name): array
    {
        $section = $this->createProgrammeSection($app->getContainer(), $this->getEvent($app), 'image section');

        return [
            'name' => $name,
            'description' => 'desc',
            'start' => '2027-06-05T10:00',
            'end' => '2027-06-05T12:00',
            'capacity' => '5',
            'sectionId' => (string)$section->id,
            'roles' => ['ist'],
        ];
    }

    /**
     * PHP hands over an empty body when the request is larger than post_max_size.
     */
    private function createOversizedRequest(string $path): ServerRequestInterface
    {
        $postMaxSize = ini_parse_quantity((string)ini_get('post_max_size'));
        self::assertGreaterThan(0, $postMaxSize, 'post_max_size is unlimited');

        return $this->createRequest($path, 'POST')->withHeader('Content-Length', (string)($postMaxSize + 1));
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function expectedTooLargeFlash(App $app): string
    {
        $postMaxSize = ini_parse_quantity((string)ini_get('post_max_size'));

        return str_replace(
            '%max%',
            max(1, intdiv($postMaxSize, 1_048_576)) . ' MB',
            $this->translate($app, 'flash.error.programmeRequestTooLarge'),
        );
    }

    private function upload(
        string $bytes,
        string $clientName,
        string $clientType,
        int $error = UPLOAD_ERR_OK,
    ): UploadedFile {
        $path = tempnam(sys_get_temp_dir(), 'programme-image-upload');
        if ($path === false || file_put_contents($path, $bytes) === false) {
            throw new LogicException('Cannot write the upload fixture');
        }
        $this->temporaryPaths[] = $path;

        return new UploadedFile($path, $clientName, $clientType, strlen($bytes), $error);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function getEvent(App $app): Event
    {
        return $this->getService($app, EventRepository::class)->getOneBy(['slug' => self::EVENT_SLUG]);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function getProgrammeByName(App $app, string $name): Programme
    {
        $programmes = $this->getService($app, ProgrammeRepository::class)->findActiveForEvent($this->getEvent($app));
        foreach ($programmes as $programme) {
            if ($programme->name === $name) {
                return $programme;
            }
        }

        throw new LogicException('Programme ' . $name . ' was not saved');
    }

    /**
     * Remembers the stored files of the rows, so that the test removes them afterwards.
     *
     * @param App<ContainerInterface> $app
     * @return list<ProgrammeImage>
     */
    private function imagesOf(App $app, Programme $programme): array
    {
        $images = $this->getService($app, ProgrammeImageRepository::class)->findForProgramme($programme);
        foreach ($images as $image) {
            $this->rememberStored($image->filename);
            if ($image->thumbFilename !== null) {
                $this->rememberStored($image->thumbFilename);
            }
        }

        return $images;
    }

    private function rememberStored(string $filename): string
    {
        $this->storedFilenames[] = $filename;

        return $filename;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function readStored(App $app, string $filename): string
    {
        return $this->getService($app, SaveFileHandler::class)->getFile($filename)->stream->getContents();
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function removeStoredFiles(App $app): void
    {
        $fileHandler = $this->getService($app, SaveFileHandler::class);
        foreach ($this->storedFilenames as $filename) {
            $fileHandler->deleteFile($filename);
        }
        $this->storedFilenames = [];
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createProgramme(App $app, Event $event): Programme
    {
        $programme = new Programme();
        $programme->name = 'image admin programme';
        $programme->description = 'description';
        $programme->start = DateTimeUtils::getDateTime('2027-06-05 10:00');
        $programme->end = DateTimeUtils::getDateTime('2027-06-05 12:00');
        $programme->capacity = 5;
        $programme->isAutoenrolled = false;
        $programme->event = $event;
        $programme->section = $this->createProgrammeSection($app->getContainer(), $event, 'Program');
        $programme->setTargetRoles([ParticipantRole::Ist]);
        $this->getService($app, ProgrammeRepository::class)->persist($programme);

        return $programme;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function persistImage(
        App $app,
        Programme $programme,
        string $filename,
        int $sortOrder,
        string $alt = '',
        ?string $thumbFilename = null,
    ): ProgrammeImage {
        $image = new ProgrammeImage();
        $image->programme = $programme;
        $image->filename = $filename;
        $image->thumbFilename = $thumbFilename;
        $image->alt = $alt;
        $image->sortOrder = $sortOrder;
        $this->getService($app, ProgrammeImageRepository::class)->persist($image);

        return $image;
    }

    /**
     * @return App<ContainerInterface>
     */
    private function createLoggedAdmin(string $email): App
    {
        $app = $this->getTestApp();
        $user = $this->getService($app, UserService::class)->registerEmailUser($email, $this->getEvent($app));
        $user->role = UserRole::ProgrammeAdmin;
        $this->getService($app, UserRepository::class)->persist($user);

        $loginToken = new LoginToken();
        $loginToken->token = bin2hex(random_bytes(16));
        $loginToken->user = $user;
        $loginToken->used = false;
        $this->getService($app, LoginTokenRepository::class)->persist($loginToken);

        $response = $app->handle($this->createRequest(
            '/v2/event/' . self::EVENT_SLUG . '/tryLogin/' . $loginToken->token,
        ));
        self::assertSame(302, $response->getStatusCode());

        return $this->getTestApp(false);
    }
}
