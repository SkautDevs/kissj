<?php declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Participant\Ist\IstRepository;
use kissj\Participant\ParticipantRepository;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserRole;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class FileDownloadTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-event-slug';
    private const string BASE_URL = '/v2/event/' . self::TEST_EVENT_SLUG;

    public function testOwnerCanDownloadOwnFile(): void
    {
        $app = $this->getTestApp();
        $container = $this->getContainer($app);

        $uniqueFilename = md5(random_bytes(16));
        $user = $this->registerUser($container, 'owner-download@example.com');
        $this->createIstWithFile($container, $user, 'parentalConsent', $uniqueFilename);

        $_SESSION['user']['id'] = $user->id;
        $app = $this->getTestApp(false);

        // Middleware passes (owner), file doesn't exist on disk — RuntimeException from LazyOpenStream
        try {
            $app->handle($this->createRequest(
                self::BASE_URL . '/showFile/' . $uniqueFilename,
            ));
            self::fail('Expected RuntimeException for missing file on disk');
        } catch (\RuntimeException $e) {
            self::assertStringContainsString($uniqueFilename, $e->getMessage());
        }
    }

    public function testAdminCanDownloadAnyFile(): void
    {
        $app = $this->getTestApp();
        $container = $this->getContainer($app);

        $uniqueFilename = md5(random_bytes(16));
        $ownerUser = $this->registerUser($container, 'file-owner@example.com');
        $this->createIstWithFile($container, $ownerUser, 'parentalConsent', $uniqueFilename);

        $adminUser = $this->createAdminUser($app);
        $adminUser->status = UserStatus::Open;
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        $userRepository->persist($adminUser);

        $_SESSION['user']['id'] = $adminUser->id;
        $app = $this->getTestApp(false);

        // Middleware passes (admin), file doesn't exist on disk — RuntimeException from LazyOpenStream
        try {
            $app->handle($this->createRequest(
                self::BASE_URL . '/showFile/' . $uniqueFilename,
            ));
            self::fail('Expected RuntimeException for missing file on disk');
        } catch (\RuntimeException $e) {
            self::assertStringContainsString($uniqueFilename, $e->getMessage());
        }
    }

    public function testNonOwnerNonAdminIsDenied(): void
    {
        $app = $this->getTestApp();
        $container = $this->getContainer($app);

        $uniqueFilename = md5(random_bytes(16));
        $ownerUser = $this->registerUser($container, 'file-owner-deny@example.com');
        $this->createIstWithFile($container, $ownerUser, 'parentalConsent', $uniqueFilename);

        $otherUser = $this->registerUser($container, 'other-user@example.com');
        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        $userService->createParticipantSetRole($otherUser, 'ist');

        $_SESSION['user']['id'] = $otherUser->id;
        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(
            self::BASE_URL . '/showFile/' . $uniqueFilename,
        ));

        self::assertSame(302, $response->getStatusCode());
    }

    public function testFileNotFoundRedirects(): void
    {
        $app = $this->getTestApp();
        $container = $this->getContainer($app);

        $user = $this->registerUser($container, 'notfound-test@example.com');
        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        $userService->createParticipantSetRole($user, 'ist');

        $_SESSION['user']['id'] = $user->id;
        $app = $this->getTestApp(false);

        $uniqueFilename = md5(random_bytes(16));
        $response = $app->handle($this->createRequest(
            self::BASE_URL . '/showFile/' . $uniqueFilename,
        ));

        self::assertSame(302, $response->getStatusCode());
    }

    public function testSetUploadedFileStoresAllFields(): void
    {
        $app = $this->getTestApp();
        $container = $this->getContainer($app);

        $user = $this->registerUser($container, 'set-upload-test@example.com');
        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        /** @var IstRepository $istRepository */
        $istRepository = $container->get(IstRepository::class);
        $ist = $istRepository->get($participant->id);

        $ist->setUploadedFile('parentalConsent', 'abc123.pdf', 'consent.pdf', 'application/pdf');
        $istRepository->persist($ist);

        $refreshed = $istRepository->get($ist->id);
        self::assertSame('abc123.pdf', $refreshed->uploadedParentalConsentFilename);
        self::assertSame('consent.pdf', $refreshed->uploadedParentalConsentOriginalFilename);
        self::assertSame('application/pdf', $refreshed->uploadedParentalConsentContenttype);
    }

    public function testSetUploadedFileRejectsUnknownId(): void
    {
        $app = $this->getTestApp();
        $container = $this->getContainer($app);

        $user = $this->registerUser($container, 'unknown-id-test@example.com');
        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        /** @var IstRepository $istRepository */
        $istRepository = $container->get(IstRepository::class);
        $ist = $istRepository->get($participant->id);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unknown file item id: invalidField');

        $ist->setUploadedFile('invalidField', 'file.pdf', 'file.pdf', 'application/pdf');
    }

    public function testGetUploadedFilenameReturnsStoredValue(): void
    {
        $app = $this->getTestApp();
        $container = $this->getContainer($app);

        $user = $this->registerUser($container, 'get-filename-test@example.com');
        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        /** @var IstRepository $istRepository */
        $istRepository = $container->get(IstRepository::class);
        $ist = $istRepository->get($participant->id);

        self::assertNull($ist->getUploadedFilename('hospitalConsent'));

        $ist->setUploadedFile('hospitalConsent', 'hospital-abc.pdf', 'hospital.pdf', 'image/jpeg');
        $istRepository->persist($ist);

        $refreshed = $istRepository->get($ist->id);
        self::assertSame('hospital-abc.pdf', $refreshed->getUploadedFilename('hospitalConsent'));
    }

    public function testFindParticipantByUploadedFilename(): void
    {
        $app = $this->getTestApp();
        $container = $this->getContainer($app);

        $user = $this->registerUser($container, 'find-by-file@example.com');
        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        /** @var IstRepository $istRepository */
        $istRepository = $container->get(IstRepository::class);
        $ist = $istRepository->get($participant->id);

        $uniqueFilename = md5(random_bytes(16));
        $ist->setUploadedFile('childWorkCert', $uniqueFilename, 'cert.pdf', 'application/pdf');
        $istRepository->persist($ist);

        /** @var ParticipantRepository $participantRepository */
        $participantRepository = $container->get(ParticipantRepository::class);

        $found = $participantRepository->findParticipantByUploadedFilename($uniqueFilename, $user->event);
        self::assertNotNull($found);
        self::assertSame($participant->id, $found->id);
    }

    public function testFindParticipantByUploadedFilenameReturnsNullForUnknown(): void
    {
        $app = $this->getTestApp();
        $container = $this->getContainer($app);

        $user = $this->registerUser($container, 'find-unknown@example.com');

        /** @var ParticipantRepository $participantRepository */
        $participantRepository = $container->get(ParticipantRepository::class);

        $uniqueFilename = md5(random_bytes(16));
        $found = $participantRepository->findParticipantByUploadedFilename($uniqueFilename, $user->event);
        self::assertNull($found);
    }

    private function getContainer(App $app): ContainerInterface
    {
        $container = $app->getContainer();
        if ($container === null) {
            throw new \RuntimeException('Container is null');
        }
        return $container;
    }

    private function registerUser(ContainerInterface $container, string $email): User
    {
        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);

        $event = $eventRepository->findBySlug(self::TEST_EVENT_SLUG);
        if ($event === null) {
            $event = $eventRepository->get(1);
        }

        return $userService->registerEmailUser($email, $event);
    }

    /**
     * @return \kissj\Participant\Ist\Ist
     */
    private function createIstWithFile(
        ContainerInterface $container,
        User $user,
        string $fileItemId,
        string $storedFilename,
    ): \kissj\Participant\Ist\Ist {
        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        /** @var IstRepository $istRepository */
        $istRepository = $container->get(IstRepository::class);
        $ist = $istRepository->get($participant->id);

        $ist->setUploadedFile($fileItemId, $storedFilename, 'original.pdf', 'application/pdf');
        $istRepository->persist($ist);

        return $ist;
    }
}
