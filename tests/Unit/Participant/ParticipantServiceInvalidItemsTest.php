<?php

declare(strict_types=1);

namespace Tests\Unit\Participant;

use kissj\Application\DateTimeUtils;
use kissj\Event\ContentArbiter\ContentArbiterItem;
use kissj\Event\ContentArbiterGuest;
use kissj\Mailer\Mailer;
use kissj\Mailer\MailerSettings;
use kissj\Participant\Guest\Guest;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use kissj\Participant\Troop\TroopParticipantRepository;
use kissj\Payment\PaymentService;
use kissj\Payment\QrCodeService;
use kissj\Telemetry\Metrics;
use kissj\User\LoginTokenRepository;
use kissj\User\UserRepository;
use kissj\User\UserService;
use Mockery;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Slim\Views\Twig;
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

        $invalidItems = $this->getParticipantService()->getInvalidItemsForClose($participant, new ContentArbiterGuest());

        self::assertSame(
            ['lastName', 'telephoneNumber', 'email'],
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

        self::assertFalse($this->getParticipantService()->isParticipantDataValidForClose($participant, new ContentArbiterGuest()));
    }

    public function testGetInvalidItemsForCloseReturnsEmptyListForFullyValidParticipant(): void
    {
        $participant = new Guest();
        $participant->birthDate = null;
        $participant->firstName = 'Jan';
        $participant->lastName = 'Novak';
        $participant->telephoneNumber = '+420123456789';
        $participant->email = 'jan.novak@example.com';
        $participant->arrivalDate = DateTimeUtils::getDateTime('2026-07-20');
        $participant->departureDate = DateTimeUtils::getDateTime('2026-07-25');

        self::assertSame([], $this->getParticipantService()->getInvalidItemsForClose($participant, new ContentArbiterGuest()));
        self::assertTrue($this->getParticipantService()->isParticipantDataValidForClose($participant, new ContentArbiterGuest()));
    }

    private function getParticipantService(): ParticipantService
    {
        $metrics = new Metrics();
        // UserService/Mailer are readonly, so Mockery cannot subclass them directly -
        // build real instances from mocked constructor dependencies instead, mirroring
        // the pattern used in tests/Unit/Payment/PaymentServiceTest.php.
        $mailerMock = new Mailer(
            Mockery::mock(Twig::class),
            Mockery::mock(MailerSettings::class),
            Mockery::mock(QrCodeService::class),
            Mockery::mock(TranslatorInterface::class),
            Mockery::mock(Logger::class),
            $metrics,
        );

        return new ParticipantService(
            Mockery::mock(ParticipantRepository::class),
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
        );
    }
}
