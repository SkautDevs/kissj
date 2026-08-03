<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\EventRepository;
use kissj\Participant\Ist\IstRepository;
use kissj\Participant\Participant;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use RuntimeException;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request;
use Slim\Psr7\Uri;
use Tests\AppTestCase;

class ParticipantVendorTest extends AppTestCase
{
    private const string TEST_PREFIX_URL = '/v3';
    private const string PLAIN_VENDOR_KEY = 'plain-vendor-key-12345';
    private const string HEALTH_VENDOR_KEY = 'health-vendor-key-12345';

    public function testHealthKeyReturnsHealthFields(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $this->setupEventApiKeys($container);
        $participant = $this->createParticipantWithHealthData($container);

        $request = $this->createBearerRequest(
            self::TEST_PREFIX_URL . '/vendor/participant/' . $participant->tieCode,
            self::HEALTH_VENDOR_KEY,
        );
        $response = $app->handle($request);

        self::assertEquals(200, $response->getStatusCode());
        $vendoredParticipant = $this->decodeVendoredParticipant($response);
        self::assertSame('Allergic to peanuts', $vendoredParticipant['physicalHealth']);
        self::assertSame('Mild anxiety', $vendoredParticipant['psychicalHealth']);
        self::assertSame('Antihistamine', $vendoredParticipant['medicaments']);
    }

    public function testPlainKeyWithoutHeaderOmitsHealthFieldsWithoutCrashing(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $this->setupEventApiKeys($container);
        $participant = $this->createParticipantWithHealthData($container);

        $request = $this->createBearerRequest(
            self::TEST_PREFIX_URL . '/vendor/participant/' . $participant->tieCode,
            self::PLAIN_VENDOR_KEY,
        );
        $response = $app->handle($request);

        self::assertEquals(200, $response->getStatusCode());
        $vendoredParticipant = $this->decodeVendoredParticipant($response);
        self::assertNull($vendoredParticipant['physicalHealth']);
        self::assertNull($vendoredParticipant['psychicalHealth']);
        self::assertNull($vendoredParticipant['medicaments']);
    }

    public function testPlainKeyWithAllowHealthHeaderStillOmitsHealthFields(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $this->setupEventApiKeys($container);
        $participant = $this->createParticipantWithHealthData($container);

        $request = $this->createBearerRequest(
            self::TEST_PREFIX_URL . '/vendor/participant/' . $participant->tieCode,
            self::PLAIN_VENDOR_KEY,
            ['Allow-Health' => 'true'],
        );
        $response = $app->handle($request);

        self::assertEquals(200, $response->getStatusCode());
        $vendoredParticipant = $this->decodeVendoredParticipant($response);
        self::assertNull($vendoredParticipant['physicalHealth']);
        self::assertNull($vendoredParticipant['psychicalHealth']);
        self::assertNull($vendoredParticipant['medicaments']);
    }

    public function testWrongKeyReturns401(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $this->setupEventApiKeys($container);
        $participant = $this->createParticipantWithHealthData($container);

        $request = $this->createBearerRequest(
            self::TEST_PREFIX_URL . '/vendor/participant/' . $participant->tieCode,
            'totally-wrong-key',
        );
        $response = $app->handle($request);

        self::assertEquals(401, $response->getStatusCode());
    }

    public function testMissingAuthorizationHeaderReturns401(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $this->setupEventApiKeys($container);
        $participant = $this->createParticipantWithHealthData($container);

        $request = $this->createBearerRequest(
            self::TEST_PREFIX_URL . '/vendor/participant/' . $participant->tieCode,
            null,
        );
        $response = $app->handle($request);

        self::assertEquals(401, $response->getStatusCode());
    }

    public function testKeyAmbiguousAcrossEventsIsRejected(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $eventRepository = $this->getService($app, EventRepository::class);

        $this->setupEventApiKeys($container);
        $participant = $this->createParticipantWithHealthData($container);

        // event B's health key collides with event A's plain vendor key
        $eventB = $this->getSmallTestEvent($eventRepository);
        $eventB->apiKeyVendorHealth = self::PLAIN_VENDOR_KEY;
        $eventRepository->persist($eventB);

        $request = $this->createBearerRequest(
            self::TEST_PREFIX_URL . '/vendor/participant/' . $participant->tieCode,
            self::PLAIN_VENDOR_KEY,
        );
        $response = $app->handle($request);

        self::assertEquals(401, $response->getStatusCode());
    }

    private function setupEventApiKeys(ContainerInterface $container): void
    {
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        $event = $eventRepository->findBySlug('test-event-slug');
        if ($event !== null) {
            $event->apiKeyVendor = self::PLAIN_VENDOR_KEY;
            $event->apiKeyVendorHealth = self::HEALTH_VENDOR_KEY;
            $eventRepository->persist($event);
        }
    }

    private function createParticipantWithHealthData(ContainerInterface $container): Participant
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

        $event->maximalClosedIstsCount = 100;
        $eventRepository->persist($event);

        $email = 'vendor-health-test-' . bin2hex(random_bytes(4)) . '@example.com';
        $user = $userService->registerEmailUser($email, $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $ist = $istRepository->get($participant->id);
        $ist->firstName = 'Vendor';
        $ist->lastName = 'Tested';
        $ist->nickname = 'Tester';
        $ist->permanentResidence = '123 Test St';
        $ist->gender = 'male';
        $ist->birthDate = DateTimeUtils::getDateTime('1990-01-01');
        $ist->email = $email;
        $ist->setTshirt('detail.tshirtGenderMale', 'detail.tshirtXL');
        $ist->healthProblems = 'Allergic to peanuts';
        $ist->psychicalHealthProblems = 'Mild anxiety';
        $ist->medicaments = 'Antihistamine';
        $istRepository->persist($ist);

        $user->status = UserStatus::Paid;

        return $istRepository->get($ist->id);
    }

    /**
     * @param array<string, string> $extraHeaders
     */
    private function createBearerRequest(string $path, ?string $token, array $extraHeaders = []): Request
    {
        $uri = new Uri('', '', 80, $path);
        $handle = fopen('php://temp', 'wb+');
        if ($handle === false) {
            throw new RuntimeException('opening php://temp failed');
        }
        $stream = (new StreamFactory())->createStreamFromResource($handle);

        $headerData = ['Content-Type' => 'application/json'];
        if ($token !== null) {
            $headerData['Authorization'] = 'Bearer ' . $token;
        }
        foreach ($extraHeaders as $name => $value) {
            $headerData[$name] = $value;
        }
        $headers = new Headers($headerData);

        return new Request('GET', $uri, $headers, [], [], $stream);
    }

    /**
     * The endpoint wraps the JSON-encoded VendoredParticipantType as a string
     * value under the "participant" key - unwrap both layers.
     *
     * @return array<string, mixed>
     */
    private function decodeVendoredParticipant(Response $response): array
    {
        $body = json_decode((string)$response->getBody(), true);
        self::assertIsArray($body);
        self::assertIsString($body['participant']);

        /** @var array<string, mixed> $vendoredParticipant */
        $vendoredParticipant = json_decode($body['participant'], true);
        self::assertIsArray($vendoredParticipant);

        return $vendoredParticipant;
    }
}
