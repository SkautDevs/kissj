<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\Ist\IstRepository;
use kissj\Participant\Participant;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request;
use Slim\Psr7\Uri;
use Tests\AppTestCase;

class EntryCodeCorsTest extends AppTestCase
{
    private const string TEST_PREFIX_URL = '/v3';
    private const string TEST_EVENT_SECRET = 'test-api-secret-cors-success';

    public function testPreflightOnEntryCodeRoute(): void
    {
        $app = $this->getTestApp();
        $request = $this->createRequest(self::TEST_PREFIX_URL . '/entry/code/some-uuid', 'OPTIONS')
            ->withHeader('Origin', 'https://entry.example.org');

        $response = $app->handle($request);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('https://entry.example.org', $response->getHeaderLine('Access-Control-Allow-Origin'));
    }

    public function testRejectedPostCarriesCorsHeader(): void
    {
        $app = $this->getTestApp();
        $request = $this->createJsonRequest(
            self::TEST_PREFIX_URL . '/entry/code/nonexistent-code',
            'POST',
            ['eventSecret' => 'wrong'],
        )->withHeader('Origin', 'https://entry.example.org');

        $response = $app->handle($request);

        self::assertSame(403, $response->getStatusCode());
        self::assertSame('https://entry.example.org', $response->getHeaderLine('Access-Control-Allow-Origin'));
    }

    public function testSuccessfulPostCarriesCorsHeader(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        [$event, $participant] = $this->makeEventWithParticipant($container, UserStatus::Paid);

        $request = $this->createJsonRequest(
            self::TEST_PREFIX_URL . '/entry/code/' . (string)$participant->entryCode,
            'POST',
            ['eventSecret' => (string)$event->apiKeyEntry],
        )->withHeader('Origin', 'https://entry.example.org');

        $response = $app->handle($request);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('https://entry.example.org', $response->getHeaderLine('Access-Control-Allow-Origin'));
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

        fwrite($handle, json_encode($jsonData, JSON_THROW_ON_ERROR));
        rewind($handle);

        $stream = (new StreamFactory())->createStreamFromResource($handle);
        $headers = new Headers(['Content-Type' => 'application/json']);

        return new Request($method, $uri, $headers, [], [], $stream);
    }

    /**
     * Build an event with a known apiKeyEntry and one IST participant carrying the given user status,
     * following the same fixture pattern as EntryCodePaidCheckTest::makeEventWithParticipant
     * (duplicated here rather than shared, since that helper is private to its own test class).
     *
     * @return array{0: Event, 1: Participant}
     */
    private function makeEventWithParticipant(?ContainerInterface $container, UserStatus $userStatus): array
    {
        if ($container === null) {
            throw new RuntimeException('app container not available');
        }

        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        $event = $eventRepository->findBySlug('test-event-slug');
        if ($event === null) {
            throw new RuntimeException('Test event not found');
        }

        $event->apiKeyEntry = self::TEST_EVENT_SECRET;
        $event->maximalClosedIstsCount = 100;
        $eventRepository->persist($event);

        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        /** @var IstRepository $istRepository */
        $istRepository = $container->get(IstRepository::class);
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);

        $email = 'entry-cors-success-' . bin2hex(random_bytes(4)) . '@example.com';
        $user = $userService->registerEmailUser($email, $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $ist = $istRepository->get($participant->id);
        $ist->firstName = 'Entry';
        $ist->lastName = 'CorsSuccess';
        $ist->nickname = 'Tester';
        $ist->permanentResidence = '123 Test St';
        $ist->gender = 'male';
        $ist->birthDate = DateTimeUtils::getDateTime('1990-01-01');
        $ist->email = $email;
        $istRepository->persist($ist);

        $user->status = $userStatus;
        $userRepository->persist($user);

        return [$event, $istRepository->get($ist->id)];
    }
}
