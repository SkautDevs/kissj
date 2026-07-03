<?php

declare(strict_types=1);

namespace Tests\Unit\User;

use kissj\Event\Event;
use kissj\Mailer\Mailer;
use kissj\Mailer\MailerSettings;
use kissj\Participant\ParticipantRepository;
use kissj\Payment\QrCodeService;
use kissj\Telemetry\Metrics;
use kissj\User\LoginTokenRepository;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Slim\Views\Twig;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testReturnsVariantEmailWhenExactMissing(): void
    {
        $event = Mockery::mock(Event::class);
        $variantUser = new User();
        $variantUser->email = 'foo@example.com';

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('isEmailUserExisting')->with('FOO@example.com', $event)->andReturn(false);
        $userRepository->shouldReceive('findFirstCaseInsensitiveVariant')->with('FOO@example.com', $event)->andReturn($variantUser);

        self::assertSame(
            'foo@example.com',
            $this->createUserService($userRepository)->findCaseVariantEmail('FOO@example.com', $event),
        );
    }

    public function testReturnsNullWhenExactExists(): void
    {
        $event = Mockery::mock(Event::class);

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('isEmailUserExisting')->with('foo@example.com', $event)->andReturn(true);
        $userRepository->shouldNotReceive('findFirstCaseInsensitiveVariant');

        self::assertNull($this->createUserService($userRepository)->findCaseVariantEmail('foo@example.com', $event));
    }

    public function testReturnsNullWhenNoVariantExists(): void
    {
        $event = Mockery::mock(Event::class);

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('isEmailUserExisting')->with('new@example.com', $event)->andReturn(false);
        $userRepository->shouldReceive('findFirstCaseInsensitiveVariant')->with('new@example.com', $event)->andReturn(null);

        self::assertNull($this->createUserService($userRepository)->findCaseVariantEmail('new@example.com', $event));
    }

    private function createUserService(UserRepository&MockInterface $userRepository): UserService
    {
        $metrics = new Metrics();
        $mailer = new Mailer(
            Mockery::mock(Twig::class),
            Mockery::mock(MailerSettings::class),
            Mockery::mock(QrCodeService::class),
            Mockery::mock(TranslatorInterface::class),
            Mockery::mock(Logger::class),
            $metrics,
        );

        return new UserService(
            Mockery::mock(LoginTokenRepository::class),
            Mockery::mock(ParticipantRepository::class),
            $userRepository,
            $mailer,
            $metrics,
        );
    }
}
