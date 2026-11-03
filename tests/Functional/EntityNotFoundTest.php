<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\BankPayment\BankPaymentRepository;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Orm\EntityNotFoundException;
use kissj\Participant\ParticipantRepository;
use kissj\Payment\PaymentRepository;
use kissj\Programme\ProgrammeRepository;
use kissj\Programme\ProgrammeSectionRepository;
use kissj\User\UserRole;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class EntityNotFoundTest extends AppTestCase
{
    private const int UNKNOWN_ID = 999999;

    public function testUnknownParticipantOnAdminRouteRendersNotFoundPage(): void
    {
        $app = $this->getTestApp();
        $event = $this->getOwnEvent($app);
        $loggedInApp = $this->loginAdminForEvent($app, $event);

        $response = $loggedInApp->handle(
            $this->createRequest('/v2/event/test-event-slug/admin/' . self::UNKNOWN_ID . '/mend'),
        );

        self::assertSame(404, $response->getStatusCode());
        self::assertStringContainsString('card card-half', (string)$response->getBody());
    }

    public function testUnknownPaymentForSubAdminRendersNotFoundPage(): void
    {
        $app = $this->getTestApp();
        $event = $this->getOwnEvent($app);
        $loggedInApp = $this->loginAdminForEvent($app, $event, UserRole::ContingentAdminCs);

        $response = $loggedInApp->handle(
            $this->createRequest(
                '/v3/event/test-event-slug/admin/payments/confirmPayment/' . self::UNKNOWN_ID,
                'POST',
            ),
        );

        self::assertSame(404, $response->getStatusCode());
        self::assertStringContainsString('card card-half', (string)$response->getBody());
    }

    public function testParticipantRepositoryThrowsEntityNotFoundException(): void
    {
        $app = $this->getTestApp();
        $event = $this->getOwnEvent($app);

        $this->expectException(EntityNotFoundException::class);
        $this->getService($app, ParticipantRepository::class)->getParticipantById(self::UNKNOWN_ID, $event);
    }

    public function testPaymentRepositoryThrowsEntityNotFoundException(): void
    {
        $app = $this->getTestApp();
        $event = $this->getOwnEvent($app);

        $this->expectException(EntityNotFoundException::class);
        $this->getService($app, PaymentRepository::class)->getById(self::UNKNOWN_ID, $event);
    }

    public function testBankPaymentRepositoryThrowsEntityNotFoundException(): void
    {
        $app = $this->getTestApp();
        $event = $this->getOwnEvent($app);

        $this->expectException(EntityNotFoundException::class);
        $this->getService($app, BankPaymentRepository::class)->getById(self::UNKNOWN_ID, $event);
    }

    public function testProgrammeRepositoryThrowsEntityNotFoundException(): void
    {
        $app = $this->getTestApp();
        $event = $this->getOwnEvent($app);

        $this->expectException(EntityNotFoundException::class);
        $this->getService($app, ProgrammeRepository::class)->getActiveById(self::UNKNOWN_ID, $event);
    }

    public function testProgrammeSectionRepositoryThrowsEntityNotFoundException(): void
    {
        $app = $this->getTestApp();
        $event = $this->getOwnEvent($app);

        $this->expectException(EntityNotFoundException::class);
        $this->getService($app, ProgrammeSectionRepository::class)->getForEventById(self::UNKNOWN_ID, $event);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function getOwnEvent(App $app): Event
    {
        return $this->getService($app, EventRepository::class)->get(1);
    }
}
