<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\User\User;
use kissj\User\UserLoginType;
use kissj\User\UserRepository;
use kissj\User\UserStatus;
use Tests\AppTestCase;

class UserRepositoryTest extends AppTestCase
{
    public function testFindFirstCaseInsensitiveVariant(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();

        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        $event = $eventRepository->get(1);

        // unique per run - the functional test database persists between runs
        $lowercaseEmail = 'repo.variant.' . bin2hex(random_bytes(4)) . '@example.com';

        $user = new User();
        $user->event = $event;
        $user->email = $lowercaseEmail;
        $user->loginType = UserLoginType::Email;
        $userRepository->persist($user);

        $variant = $userRepository->findFirstCaseInsensitiveVariant(strtoupper($lowercaseEmail), $event);
        self::assertNotNull($variant);
        self::assertSame($lowercaseEmail, $variant->email);

        // the exact same string is not a "variant"
        self::assertNull($userRepository->findFirstCaseInsensitiveVariant($lowercaseEmail, $event));

        // unrelated address finds nothing
        self::assertNull($userRepository->findFirstCaseInsensitiveVariant(
            'repo.other.' . bin2hex(random_bytes(4)) . '@example.com',
            $event,
        ));
    }

    public function testClaimStatusChangeIsAnAtomicCompareAndSwap(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();

        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        $event = $eventRepository->get(1);

        $user = new User();
        $user->event = $event;
        $user->email = 'repo.claim.' . bin2hex(random_bytes(4)) . '@example.com';
        $user->loginType = UserLoginType::Email;
        $user->status = UserStatus::Paid;
        $userRepository->persist($user);

        // first claim from the expected status wins and actually flips the row
        self::assertTrue($userRepository->claimStatusChange($user, UserStatus::Paid, UserStatus::Open));
        self::assertSame(UserStatus::Open, $userRepository->get($user->id)->status);

        // a racing/duplicate claim against the now-stale expected status finds nothing to update
        self::assertFalse($userRepository->claimStatusChange($user, UserStatus::Paid, UserStatus::Open));
        self::assertSame(UserStatus::Open, $userRepository->get($user->id)->status);
    }
}
