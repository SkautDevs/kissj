<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Participant\ParticipantRepository;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Tests\AppTestCase;

class CsvFormulaEscapeTest extends AppTestCase
{
    private const string BASE_URL = '/v2/event/test-event-slug';

    public function testHealthExportEscapesFormulaCells(): void
    {
        $app = $this->getTestApp();

        $eventRepository = $this->getService($app, EventRepository::class);
        $userService = $this->getService($app, UserService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);

        $testEvent = $eventRepository->get(1);

        // Create IST with formula-triggering name
        $email1 = 'ist-formula-' . bin2hex(random_bytes(6)) . '@example.com';
        $user1 = $userService->registerEmailUser($email1, $testEvent);
        $participant1 = $userService->createParticipantSetRole($user1, 'ist');
        $participant1->firstName = '=2+5';
        $participant1->lastName = 'Injected';
        $participantRepository->persist($participant1);
        $user1->status = UserStatus::Paid;
        $userRepository->persist($user1);

        // Create IST with normal name
        $email2 = 'ist-normal-' . bin2hex(random_bytes(6)) . '@example.com';
        $user2 = $userService->registerEmailUser($email2, $testEvent);
        $participant2 = $userService->createParticipantSetRole($user2, 'ist');
        $participant2->firstName = 'Plainname';
        $participant2->lastName = 'Normal';
        $participantRepository->persist($participant2);
        $user2->status = UserStatus::Paid;
        $userRepository->persist($user2);

        $adminUser = $this->createAdminUser($app);
        $_SESSION['user'] = ['id' => $adminUser->id];
        $app = $this->getTestApp(false);

        $response = $app->handle(
            $this->createRequest(self::BASE_URL . '/admin/export/health', 'GET'),
        );

        self::assertSame(200, $response->getStatusCode());
        $body = (string)$response->getBody();

        self::assertStringContainsString("'=2+5", $body, 'formula cell must be escaped with a leading apostrophe');
        self::assertStringNotContainsString(",=2+5", $body, 'no unescaped formula cell may remain');
        self::assertStringContainsString('Plainname', $body);
        self::assertStringNotContainsString("'Plainname", $body, 'normal cells must stay untouched');
    }
}
