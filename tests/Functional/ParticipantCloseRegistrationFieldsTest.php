<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\EventRepository;
use kissj\Participant\Guest\GuestRepository;
use kissj\Participant\ParticipantService;
use kissj\User\User;
use kissj\User\UserService;
use Psr\Container\ContainerInterface;
use Tests\AppTestCase;

class ParticipantCloseRegistrationFieldsTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-event-slug';

    public function testIsCloseRegistrationValidListsMissingFields(): void
    {
        $app = $this->getTestApp();

        $user = $this->registerUser($app->getContainer(), 'close-registration-fields@example.com');

        $userService = $this->getService($app, UserService::class);
        $participant = $userService->createParticipantSetRole($user, 'guest');

        $guestRepository = $this->getService($app, GuestRepository::class);
        $guest = $guestRepository->get($participant->id);

        $guest->firstName = 'Jan';
        // lastName intentionally left unset - missing required field
        $guest->email = 'jan.novak@example.com';
        // telephoneNumber intentionally left unset - missing required field
        $guest->arrivalDate = DateTimeUtils::getDateTime('2026-07-20');
        $guest->departureDate = DateTimeUtils::getDateTime('2026-07-25');
        $guestRepository->persist($guest);

        $participantService = $this->getService($app, ParticipantService::class);
        $result = $participantService->isCloseRegistrationValid($guest);

        self::assertFalse($result->isValid);

        $fieldsWarnings = array_values(array_filter(
            $result->warnings,
            static fn (array $warning): bool => $warning['key'] === 'flash.warning.noLockFields',
        ));
        self::assertCount(1, $fieldsWarnings);

        self::assertSame(
            ['detail.surname', 'detail.phone'],
            $fieldsWarnings[0]['params']['%fields%'],
        );

        self::assertNotContains('flash.warning.noLock', array_column($result->warnings, 'key'));
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
}
