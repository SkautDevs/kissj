<?php

declare(strict_types=1);

namespace Tests\Unit\Participant;

use kissj\Application\DateTimeUtils;
use kissj\Event\ContentArbiterGuest;
use kissj\Event\EventRepository;
use kissj\Mailer\Mailer;
use kissj\Mailer\MailerSettings;
use kissj\Participant\Guest\Guest;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use kissj\Participant\Troop\TroopParticipantRepository;
use kissj\Participant\TshirtService;
use kissj\Payment\PaymentService;
use kissj\Payment\QrCodeService;
use kissj\Telemetry\Metrics;
use kissj\User\LoginTokenRepository;
use kissj\User\UserRepository;
use kissj\User\UserService;
use Mockery;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ParticipantServicePhoneTest extends TestCase
{
    #[DataProvider('phoneProvider')]
    public function testPhoneIsNormalizedOnSave(string $input, string $expected): void
    {
        $repository = Mockery::mock(ParticipantRepository::class);
        $repository->shouldReceive('persist')->once();
        $participant = new Guest();

        $this->getParticipantService($repository)->addParamsIntoParticipant(
            $participant,
            ['telephoneNumber' => $input],
            [(new ContentArbiterGuest())->phone],
        );

        self::assertSame($expected, $participant->telephoneNumber);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function phoneProvider(): array
    {
        return [
            'bare czech' => ['603123456', '+420 603 123 456'],
            'bare czech spaced' => ['603 123 456', '+420 603 123 456'],
            'czech prefix compact' => ['+420603123456', '+420 603 123 456'],
            'slovak prefix odd spacing' => ['+421 903123456', '+421 903 123 456'],
            'surrounding whitespace and tab' => ["  +420\t603 123 456 ", '+420 603 123 456'],
            'foreign keeps grouping' => ['+49  30   1234567', '+49 30 1234567'],
            'foreign trimmed' => [' +44 20 7946 0958 ', '+44 20 7946 0958'],
            'short number untouched' => ['12345', '12345'],
            'garbage untouched' => ['call me', 'call me'],
            'double zero czech' => ['00420 603 123 456', '+420 603 123 456'],
            'double zero slovak compact' => ['00421903123456', '+421 903 123 456'],
            'double zero foreign keeps grouping' => ['0049 30 1234567', '+49 30 1234567'],
            'bare 420 prefix' => ['420603123456', '+420 603 123 456'],
            'non-breaking spaces' => ["603\u{00A0}123\u{00A0}456", '+420 603 123 456'],
            'wrong digit count stays' => ['+420 60312345', '+420 60312345'],
        ];
    }

    public function testNullPhoneStaysNull(): void
    {
        $repository = Mockery::mock(ParticipantRepository::class);
        $repository->shouldReceive('persist')->once();
        $participant = new Guest();

        $this->getParticipantService($repository)->addParamsIntoParticipant(
            $participant,
            [],
            [(new ContentArbiterGuest())->phone],
        );

        self::assertNull($participant->telephoneNumber);
    }

    #[DataProvider('blankPhoneProvider')]
    public function testBlankPhoneSavesAsNull(string $input): void
    {
        $participant = new Guest();
        $participant->setTelephoneNumberNormalized($input);

        self::assertNull($participant->telephoneNumber);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function blankPhoneProvider(): array
    {
        return [
            'empty' => [''],
            'spaces' => ['   '],
            'non-breaking space' => ["\u{00A0}"],
        ];
    }

    public function testNullStaysNullInEntitySetter(): void
    {
        $participant = new Guest();
        $participant->setTelephoneNumberNormalized(null);

        self::assertNull($participant->telephoneNumber);
    }

    public function testCloseValidationAcceptsValuesThatNormalizationFixes(): void
    {
        $service = $this->getParticipantService(Mockery::mock(ParticipantRepository::class));
        $ca = new ContentArbiterGuest();
        $ca->phone->allowed = true;

        foreach (['603123456', '00420603123456', '+420 603 123 456'] as $valid) {
            self::assertNotContains($ca->phone, $service->getInvalidItemsForClose($this->createGuestWithPhone($valid), $ca));
        }

        foreach (['0905 123 456', '12345'] as $invalid) {
            self::assertContains($ca->phone, $service->getInvalidItemsForClose($this->createGuestWithPhone($invalid), $ca));
        }
    }

    private function createGuestWithPhone(string $telephoneNumber): Guest
    {
        $guest = new Guest();
        $guest->birthDate = null;
        $guest->firstName = 'Jan';
        $guest->lastName = 'Novak';
        $guest->telephoneNumber = $telephoneNumber;
        $guest->email = 'jan.novak@example.com';
        $guest->arrivalDate = DateTimeUtils::getDateTime('2026-07-20');
        $guest->departureDate = DateTimeUtils::getDateTime('2026-07-25');

        return $guest;
    }

    private function getParticipantService(ParticipantRepository $participantRepository): ParticipantService
    {
        $metrics = new Metrics();
        $mailerMock = new Mailer(
            Mockery::mock(MailerInterface::class),
            Mockery::mock(MailerSettings::class),
            Mockery::mock(QrCodeService::class),
            Mockery::mock(TranslatorInterface::class),
            Mockery::mock(Logger::class),
            $metrics,
        );

        return new ParticipantService(
            $participantRepository,
            Mockery::mock(TroopParticipantRepository::class),
            Mockery::mock(PaymentService::class),
            new UserService(
                Mockery::mock(LoginTokenRepository::class),
                Mockery::mock(ParticipantRepository::class),
                Mockery::mock(UserRepository::class),
                $mailerMock,
                $metrics,
            ),
            $mailerMock,
            $metrics,
            new TshirtService(Mockery::mock(TranslatorInterface::class)),
            Mockery::mock(EventRepository::class),
            Mockery::mock(UserRepository::class),
        );
    }
}
