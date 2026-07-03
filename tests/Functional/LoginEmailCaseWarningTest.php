<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\User\User;
use kissj\User\UserLoginType;
use kissj\User\UserRepository;
use Tests\AppTestCase;

class LoginEmailCaseWarningTest extends AppTestCase
{
    public function testWarnsAboutCaseVariantOnLogin(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();

        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        $event = $eventRepository->get(1);

        // unique per run - the functional test database persists between runs
        $lowercaseEmail = 'case.variant.' . bin2hex(random_bytes(4)) . '@example.com';

        $user = new User();
        $user->event = $event;
        $user->email = $lowercaseEmail;
        $user->loginType = UserLoginType::Email;
        $userRepository->persist($user);

        $app->handle($this->createRequest(
            '/v2/event/test-event-slug/login',
            'POST',
            ['email' => strtoupper($lowercaseEmail)],
        ));

        self::assertTrue($this->hasWarningFlashContaining($lowercaseEmail));
    }

    public function testNoWarningForFreshEmail(): void
    {
        $app = $this->getTestApp();

        $app->handle($this->createRequest(
            '/v2/event/test-event-slug/login',
            'POST',
            ['email' => 'fresh.' . bin2hex(random_bytes(4)) . '@example.com'],
        ));

        self::assertFalse($this->hasWarningFlashContaining('@example.com'));
    }

    private function hasWarningFlashContaining(string $needle): bool
    {
        /** @var array<array{type: string, message: string}> $messages */
        $messages = $_SESSION['flashMessages'] ?? [];
        foreach ($messages as $message) {
            if ($message['type'] === 'warning' && str_contains($message['message'], $needle)) {
                return true;
            }
        }

        return false;
    }
}
