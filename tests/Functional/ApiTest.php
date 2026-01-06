<?php declare(strict_types=1);

namespace Tests\Functional;

use DateTimeImmutable;
use kissj\Entry\EntryStatus;
use kissj\Event\EventRepository;
use kissj\Participant\Ist\IstRepository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\User\User;
use kissj\User\UserLoginType;
use kissj\User\UserRepository;
use kissj\User\UserRole;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Slim\App;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request;
use Slim\Psr7\Uri;
use Tests\AppTestCase;

class ApiTest extends AppTestCase
{
    private const string TEST_PREFIX_URL = '/v3';
    private const string TEST_EVENT_SECRET = 'test-api-secret-12345';

    public function testEntryWithInvalidEntryCode(): void
    {
        $app = $this->getTestApp();
        $container = $this->getContainer($app);
        
        // Set up event with API secret
        $this->setupEventApiSecret($container);
        
        $request = $this->createJsonRequest(
            self::TEST_PREFIX_URL . '/entry/code/nonexistent-code',
            'POST',
            ['eventSecret' => self::TEST_EVENT_SECRET],
        );
        $response = $app->handle($request);

        $this->assertEquals(403, $response->getStatusCode());
        $body = json_decode((string)$response->getBody(), true);
        $this->assertEquals(EntryStatus::ENTRY_STATUS_INVALID->value, $body['status']);
        $this->assertEquals('participant not found', $body['reason']);
    }

    public function testEntryWithInvalidEventSecret(): void
    {
        $app = $this->getTestApp();
        $container = $this->getContainer($app);
        
        // Set up event and create a participant with entry code
        $this->setupEventApiSecret($container);
        $participant = $this->createPaidParticipant($container);
        
        $request = $this->createJsonRequest(
            self::TEST_PREFIX_URL . '/entry/code/' . $participant->entryCode,
            'POST',
            ['eventSecret' => 'wrong-secret'],
        );
        $response = $app->handle($request);

        $this->assertEquals(403, $response->getStatusCode());
        $body = json_decode((string)$response->getBody(), true);
        $this->assertEquals(EntryStatus::ENTRY_STATUS_INVALID->value, $body['status']);
        $this->assertEquals('invalid event secret', $body['reason']);
    }

    public function testEntryFirstTime(): void
    {
        $app = $this->getTestApp();
        $container = $this->getContainer($app);
        
        // Set up event and create a participant
        $this->setupEventApiSecret($container);
        $participant = $this->createPaidParticipant($container);
        
        $request = $this->createJsonRequest(
            self::TEST_PREFIX_URL . '/entry/code/' . $participant->entryCode,
            'POST',
            ['eventSecret' => self::TEST_EVENT_SECRET],
        );
        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string)$response->getBody(), true);
        $this->assertEquals(EntryStatus::ENTRY_STATUS_VALID->value, $body['status']);
        $this->assertArrayHasKey('fullName', $body);
        $this->assertArrayHasKey('email', $body);
    }

    public function testEntrySecondTime(): void
    {
        $app = $this->getTestApp();
        $container = $this->getContainer($app);
        
        // Set up event and create a participant
        $this->setupEventApiSecret($container);
        $participant = $this->createPaidParticipant($container);
        
        // First entry
        $request1 = $this->createJsonRequest(
            self::TEST_PREFIX_URL . '/entry/code/' . $participant->entryCode,
            'POST',
            ['eventSecret' => self::TEST_EVENT_SECRET],
        );
        $response1 = $app->handle($request1);
        $this->assertEquals(200, $response1->getStatusCode());
        
        // Second entry (same participant)
        $request2 = $this->createJsonRequest(
            self::TEST_PREFIX_URL . '/entry/code/' . $participant->entryCode,
            'POST',
            ['eventSecret' => self::TEST_EVENT_SECRET],
        );
        $response2 = $this->getTestApp(false)->handle($request2);

        $this->assertEquals(200, $response2->getStatusCode());
        $body = json_decode((string)$response2->getBody(), true);
        $this->assertEquals(EntryStatus::ENTRY_STATUS_USED->value, $body['status']);
        $this->assertArrayHasKey('entryDateTime', $body);
    }

    private function getContainer(App $app): ContainerInterface
    {
        $container = $app->getContainer();
        if ($container === null) {
            throw new RuntimeException('Container is null');
        }
        return $container;
    }

    /**
     * Create a request with JSON body (for API endpoints that read from body stream)
     * @param array<string, mixed> $jsonData
     */
    private function createJsonRequest(string $path, string $method, array $jsonData): Request
    {
        $uri = new Uri('', '', 80, $path);
        $handle = fopen('php://temp', 'wb+');
        if ($handle === false) {
            throw new RuntimeException('opening php://temp failed');
        }
        
        // Write JSON to the stream
        fwrite($handle, json_encode($jsonData, JSON_THROW_ON_ERROR));
        rewind($handle);
        
        $stream = (new StreamFactory())->createStreamFromResource($handle);
        $headers = new Headers(['Content-Type' => 'application/json']);
        
        return new Request($method, $uri, $headers, [], [], $stream);
    }

    private function setupEventApiSecret(ContainerInterface $container): void
    {
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        $event = $eventRepository->findBySlug('test-event-slug');
        if ($event !== null) {
            $event->apiSecret = self::TEST_EVENT_SECRET;
            $eventRepository->persist($event);
        }
    }

    private function createPaidParticipant(ContainerInterface $container): Participant
    {
        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        /** @var IstRepository $istRepository */
        $istRepository = $container->get(IstRepository::class);
        
        $event = $eventRepository->findBySlug('test-event-slug');
        if ($event === null) {
            throw new RuntimeException('Test event not found');
        }
        
        // Increase IST capacity
        $event->maximalClosedIstsCount = 100;
        $eventRepository->persist($event);
        
        // Create user and IST participant
        $email = 'entry-test-' . bin2hex(random_bytes(4)) . '@example.com';
        $user = $userService->registerEmailUser($email, $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');
        
        // Get as IST and fill required fields
        $ist = $istRepository->get($participant->id);
        $ist->firstName = 'Entry';
        $ist->lastName = 'Test';
        $ist->nickname = 'Tester';
        $ist->permanentResidence = '123 Test St';
        $ist->gender = 'male';
        $ist->birthDate = new DateTimeImmutable('1990-01-01');
        $ist->email = $email;
        $istRepository->persist($ist);
        
        // Set user as paid (required for entry)
        $user->status = UserStatus::Paid;
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        $userRepository->persist($user);
        
        return $istRepository->get($ist->id);
    }

    public function testChangeAdminNote(): void
    {
        // First app instance to set up data
        $app = $this->getTestApp();
        $container = $this->getContainer($app);

        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        $event = $eventRepository->findBySlug('test-event-slug');
        $this->assertNotNull($event);

        // Create admin user
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        $adminUser = new User();
        $adminUser->event = $event;
        $adminUser->role = UserRole::Admin;
        $adminUser->email = 'admin-note-test@example.com';
        $adminUser->loginType = UserLoginType::Email;
        $adminUser->status = UserStatus::Open;
        $userRepository->persist($adminUser);

        // Create a participant to add note to
        $participant = $this->createPaidParticipant($container);

        // Log in as admin (set session) BEFORE creating new app instance
        // UserRegeneration reads session at construction time
        $_SESSION['user']['id'] = $adminUser->id;

        // Close session so it can be re-opened by the new app instance
        session_write_close();

        // Get fresh app instance so UserRegeneration picks up the session
        $app = $this->getTestApp(false);
        $container = $this->getContainer($app);

        // Make request to change admin note
        $noteText = 'This is a test admin note';
        $request = $this->createFormRequest(
            '/v3/event/test-event-slug/admin/' . $participant->id . '/adminNote',
            'POST',
            ['adminNote' => $noteText],
        );
        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string)$response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('adminNote', $body);
        $this->assertEquals($noteText, $body['adminNote']);

        // Verify note was persisted
        /** @var ParticipantRepository $participantRepository */
        $participantRepository = $container->get(ParticipantRepository::class);
        $updatedParticipant = $participantRepository->get($participant->id);
        $this->assertEquals($noteText, $updatedParticipant->adminNote);
    }

    public function testChangeAdminNoteRequiresAdmin(): void
    {
        // First app instance to set up data
        $app = $this->getTestApp();
        $container = $this->getContainer($app);

        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        $event = $eventRepository->findBySlug('test-event-slug');
        $this->assertNotNull($event);

        // Create regular (non-admin) user
        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        $regularUser = $userService->registerEmailUser('regular-user@example.com', $event);
        $regularUser->role = UserRole::Participant;
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        $userRepository->persist($regularUser);

        // Create a participant
        $participant = $this->createPaidParticipant($container);

        // Log in as regular user BEFORE creating new app instance
        $_SESSION['user']['id'] = $regularUser->id;

        // Close session so it can be re-opened by the new app instance
        session_write_close();

        // Get fresh app instance so UserRegeneration picks up the session
        $app = $this->getTestApp(false);

        // Try to change admin note - should be rejected
        $request = $this->createFormRequest(
            '/v3/event/test-event-slug/admin/' . $participant->id . '/adminNote',
            'POST',
            ['adminNote' => 'Should not work'],
        );
        $response = $app->handle($request);

        // Should redirect (302) because user is not admin
        $this->assertEquals(302, $response->getStatusCode());
    }

    /**
     * Create a request with form body (for endpoints that read from parsed body)
     * @param array<string, string> $formData
     */
    private function createFormRequest(string $path, string $method, array $formData): Request
    {
        $uri = new Uri('', '', 80, $path);
        $handle = fopen('php://temp', 'wb+');
        if ($handle === false) {
            throw new RuntimeException('opening php://temp failed');
        }
        $stream = (new StreamFactory())->createStreamFromResource($handle);
        $headers = new Headers(['Content-Type' => 'application/x-www-form-urlencoded']);
        
        $request = new Request($method, $uri, $headers, [], [], $stream);
        return $request->withParsedBody($formData);
    }
}
