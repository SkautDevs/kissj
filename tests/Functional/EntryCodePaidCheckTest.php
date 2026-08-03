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
use Slim\Psr7\Factory\ServerRequestFactory;
use Tests\AppTestCase;

class EntryCodePaidCheckTest extends AppTestCase
{
    private const string TEST_EVENT_SECRET = 'test-api-secret-paid-check';

    public function testRefusesEntryForNonPaidParticipant(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        // Arrange: an event with a known apiKeyEntry and a participant whose user status is Approved (not Paid),
        // carrying a known entryCode.
        [$event, $participant] = $this->makeEventWithParticipant($container, UserStatus::Approved);

        $response = $app->handle($this->entryCodeRequest((string)$participant->entryCode, (string)$event->apiKeyEntry));

        self::assertSame(403, $response->getStatusCode());
        $body = (string)$response->getBody();
        self::assertStringContainsString('participant not paid', $body);
    }

    public function testEntersPaidParticipant(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        [$event, $participant] = $this->makeEventWithParticipant($container, UserStatus::Paid);

        $response = $app->handle($this->entryCodeRequest((string)$participant->entryCode, (string)$event->apiKeyEntry));

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('"valid"', (string)$response->getBody());
    }

    private function entryCodeRequest(string $entryCode, string $eventSecret): \Psr\Http\Message\ServerRequestInterface
    {
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/v3/entry/code/' . $entryCode)
            ->withHeader('Content-Type', 'application/json');
        $request->getBody()->write((string)json_encode(['eventSecret' => $eventSecret]));
        $request->getBody()->rewind();

        return $request;
    }

    /**
     * Build an event with a known apiKeyEntry and one IST participant carrying the given user status,
     * following the same fixture pattern as ApiTest::createPaidParticipant/setupEventApiKeys.
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

        $this->mutateEventForTest($container, $event, [
            'apiKeyEntry' => self::TEST_EVENT_SECRET,
            'maximalClosedIstsCount' => 100,
        ]);

        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        /** @var IstRepository $istRepository */
        $istRepository = $container->get(IstRepository::class);
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);

        $email = 'entry-paid-check-' . bin2hex(random_bytes(4)) . '@example.com';
        $user = $userService->registerEmailUser($email, $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $ist = $istRepository->get($participant->id);
        $ist->firstName = 'Entry';
        $ist->lastName = 'PaidCheck';
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
