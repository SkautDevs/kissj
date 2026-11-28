<?php

declare(strict_types=1);

namespace Tests\Unit\Participant;

use kissj\Application\DateTimeUtils;
use kissj\Event\ContentArbiter\ContentArbiterItem;
use kissj\Event\ContentArbiterGuest;
use kissj\Event\ContentArbiterIst;
use kissj\Event\EventRepository;
use kissj\Mailer\Mailer;
use kissj\Mailer\MailerSettings;
use kissj\Participant\Guest\Guest;
use kissj\Participant\Ist\Ist;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use kissj\Participant\Troop\TroopParticipantRepository;
use kissj\Participant\TshirtService;
use kissj\Payment\PaymentRepository;
use kissj\Payment\PaymentService;
use kissj\Payment\QrCodeService;
use kissj\Programme\ProgrammeRegistrationRepository;
use kissj\Programme\ProgrammeRepository;
use kissj\Programme\ProgrammeService;
use kissj\Telemetry\Metrics;
use kissj\User\LoginTokenRepository;
use kissj\User\UserRepository;
use kissj\User\UserService;
use Mockery;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ParticipantServiceInvalidItemsTest extends TestCase
{
    public function testGetInvalidItemsForCloseCollectsMissingAndMalformedFields(): void
    {
        $participant = new Guest();
        // birthDate/telephoneNumber/email are read directly (not via getValueForField),
        // so they must be explicitly initialised - even to null - on a detached entity.
        $participant->birthDate = null;
        $participant->firstName = 'Jan';
        // lastName intentionally left unset - missing required field
        $participant->telephoneNumber = null; // missing required field
        $participant->email = 'not-an-email'; // present but malformed
        $participant->arrivalDate = DateTimeUtils::getDateTime('2026-07-20');
        $participant->departureDate = DateTimeUtils::getDateTime('2026-07-25');

        $invalidItems = $this->getParticipantService()->getInvalidItemsForClose(
            $participant,
            new ContentArbiterGuest(),
        );

        self::assertSame(
            ['lastName', 'telephoneNumber', 'email'],
            array_map(static fn (ContentArbiterItem $item): string => $item->slug, $invalidItems),
        );
    }

    public function testEmptyStringRequiredFieldBlocksIstClose(): void
    {
        $participant = new Ist();
        $participant->birthDate = null;
        $participant->firstName = '';

        $invalidItems = $this->getParticipantService()->getInvalidItemsForClose($participant, new ContentArbiterIst());

        self::assertContains(
            'firstName',
            array_map(static fn (ContentArbiterItem $item): string => $item->slug, $invalidItems),
        );
    }

    public function testIsParticipantDataValidForCloseIsFalseWhenItemsAreInvalid(): void
    {
        $participant = new Guest();
        $participant->birthDate = null;
        $participant->firstName = 'Jan';
        $participant->telephoneNumber = null;
        $participant->email = 'not-an-email';
        $participant->arrivalDate = DateTimeUtils::getDateTime('2026-07-20');
        $participant->departureDate = DateTimeUtils::getDateTime('2026-07-25');

        self::assertFalse(
            $this->getParticipantService()->isParticipantDataValidForClose($participant, new ContentArbiterGuest()),
        );
    }

    public function testGetInvalidItemsForCloseReturnsEmptyListForFullyValidParticipant(): void
    {
        $participant = $this->createValidGuest();

        self::assertSame(
            [],
            $this->getParticipantService()->getInvalidItemsForClose($participant, new ContentArbiterGuest()),
        );
        self::assertTrue(
            $this->getParticipantService()->isParticipantDataValidForClose($participant, new ContentArbiterGuest()),
        );
    }

    public function testGetInvalidItemsForCloseFlagsFutureAndImplausiblyOldBirthDate(): void
    {
        $ca = new ContentArbiterGuest();
        $ca->birthDate->allowed = true;
        $service = $this->getParticipantService();

        $futureGuest = $this->createValidGuest();
        $futureGuest->birthDate = DateTimeUtils::getDateTime('+1 day');
        self::assertContains($ca->birthDate, $service->getInvalidItemsForClose($futureGuest, $ca));

        $tooOldGuest = $this->createValidGuest();
        $tooOldGuest->birthDate = DateTimeUtils::getDateTime('today -120 years -1 day');
        self::assertContains($ca->birthDate, $service->getInvalidItemsForClose($tooOldGuest, $ca));

        $exactBoundaryGuest = $this->createValidGuest();
        $exactBoundaryGuest->birthDate = DateTimeUtils::getDateTime('today -120 years');
        self::assertNotContains($ca->birthDate, $service->getInvalidItemsForClose($exactBoundaryGuest, $ca));

        $validGuest = $this->createValidGuest();
        $validGuest->birthDate = DateTimeUtils::getDateTime('2000-05-15');
        self::assertNotContains($ca->birthDate, $service->getInvalidItemsForClose($validGuest, $ca));
    }

    public function testAddParamsTrimsWhitespaceOnTextFieldsAndEmail(): void
    {
        $repository = Mockery::mock(ParticipantRepository::class);
        $repository->shouldReceive('persist')->once();
        $participant = new Guest();
        $ca = new ContentArbiterGuest();

        $this->getParticipantService($repository)->addParamsIntoParticipant(
            $participant,
            [
                'firstName' => '  Jan  ',
                'lastName' => "\tNovak\n",
                'email' => '   ',
            ],
            [$ca->firstName, $ca->lastName, $ca->email],
        );

        self::assertSame('Jan', $participant->firstName);
        self::assertSame('Novak', $participant->lastName);
        self::assertNull($participant->email);
    }

    private function createValidGuest(): Guest
    {
        $participant = new class () extends Guest {
            public function getAgeAtStartOfEvent(): int
            {
                return 20;
            }
        };
        $participant->birthDate = null;
        $participant->firstName = 'Jan';
        $participant->lastName = 'Novak';
        $participant->telephoneNumber = '+420603123456';
        $participant->email = 'jan.novak@example.com';
        $participant->arrivalDate = DateTimeUtils::getDateTime('2026-07-20');
        $participant->departureDate = DateTimeUtils::getDateTime('2026-07-25');

        return $participant;
    }

    private function getParticipantService(?ParticipantRepository $participantRepository = null): ParticipantService
    {
        $metrics = new Metrics();
        // UserService/Mailer are readonly, so Mockery cannot subclass them directly -
        // build real instances from mocked constructor dependencies instead, mirroring
        // the pattern used in tests/Unit/Payment/PaymentServiceTest.php.
        $mailerMock = new Mailer(
            Mockery::mock(MailerInterface::class),
            Mockery::mock(MailerSettings::class),
            Mockery::mock(QrCodeService::class),
            Mockery::mock(TranslatorInterface::class),
            Mockery::mock(Logger::class),
            $metrics,
        );

        return new ParticipantService(
            $participantRepository ?? Mockery::mock(ParticipantRepository::class),
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
            // TshirtService is readonly too - same reason as Mailer/UserService above
            new TshirtService(Mockery::mock(TranslatorInterface::class)),
            Mockery::mock(EventRepository::class),
            Mockery::mock(UserRepository::class),
            new ProgrammeService(
                Mockery::mock(ProgrammeRepository::class),
                Mockery::mock(ProgrammeRegistrationRepository::class),
            ),
            Mockery::mock(PaymentRepository::class),
        );
    }
}
