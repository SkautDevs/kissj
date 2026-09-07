<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Payment\Payment;
use kissj\Payment\PaymentRepository;
use kissj\Payment\PaymentStatus;
use kissj\User\UserRepository;
use kissj\User\UserRole;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class FinancesAdminPageTest extends AppTestCase
{
    /** @param App<ContainerInterface> $app */
    private function createPayment(
        App $app,
        Event $event,
        PaymentStatus $status,
        string $price,
        ?string $paidAt = null,
        string $scarf = Participant::SCARF_NO,
    ): void {
        $userService = $this->getService($app, UserService::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $paymentRepository = $this->getService($app, PaymentRepository::class);

        $email = 'finances-page-' . bin2hex(random_bytes(4)) . '@example.com';
        $user = $userService->registerEmailUser($email, $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');
        $participant->scarf = $scarf;
        $participantRepository->persist($participant);

        $payment = new Payment();
        $payment->participant = $participant;
        $payment->status = $status;
        $payment->price = $price;
        $payment->currency = 'Kč';
        $payment->variableSymbol = (string)random_int(1000000000, 9999999999);
        $payment->purpose = 'event fee';
        $payment->accountNumber = '';
        $payment->iban = '';
        $payment->swift = '';
        $payment->constantSymbol = '';
        $payment->note = '';
        $payment->due = DateTimeUtils::getDateTime('+14 days');
        if ($paidAt !== null) {
            $payment->paidAt = DateTimeUtils::getDateTime($paidAt);
        }
        $paymentRepository->persist($payment);
    }

    /**
     * @param App<ContainerInterface> $app
     * @return App<ContainerInterface>
     */
    private function loginAdminForEvent(App $app, Event $event, UserRole $role = UserRole::Admin): App
    {
        $userRepository = $this->getService($app, UserRepository::class);
        $adminUser = $this->createAdminUser($app, $role);
        $adminUser->status = UserStatus::Open;
        $adminUser->event = $event;
        $userRepository->persist($adminUser);

        $_SESSION['user'] = ['id' => $adminUser->id];

        return $this->getTestApp(false);
    }

    public function testFinancesPageShowsMonthlySumsAndWaitingTotal(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);

        $this->createPayment($app, $event, PaymentStatus::Paid, '450', '2026-05-10');
        $this->createPayment($app, $event, PaymentStatus::Paid, '600', '2026-05-20');
        $this->createPayment($app, $event, PaymentStatus::Waiting, '750');

        $app = $this->loginAdminForEvent($app, $event);
        $response = $app->handle($this->createRequest('/v2/event/' . $event->slug . '/admin/finances'));

        self::assertSame(200, $response->getStatusCode());
        $body = (string)$response->getBody();

        self::assertStringContainsString('přijaté platby po měsících', $body);
        self::assertStringContainsString('2026-05', $body);
        self::assertStringContainsString('1050', $body);
        self::assertStringContainsString('očekávané peníze z nezaplacených plateb', $body);
        self::assertStringContainsString('750', $body);
        // no scarf/tier section on a non-Korbo event
        self::assertStringNotContainsString('rozpad dle ceníku', $body);
    }

    public function testFinancesPageShowsKorboScarfAndTierSection(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);

        $this->setEventType($app->getContainer(), 'korbo', 'korbo-finances-page');
        $event = $eventRepository->findBySlug('korbo-finances-page');
        self::assertNotNull($event);
        $event->defaultPrice = 450;
        $eventRepository->persist($event);
        $event = $eventRepository->get($event->id);

        $this->createPayment($app, $event, PaymentStatus::Paid, '600', '2026-05-12', Participant::SCARF_YES);
        $this->createPayment($app, $event, PaymentStatus::Waiting, '750', null, Participant::SCARF_YES);

        $app = $this->loginAdminForEvent($app, $event);
        $response = $app->handle($this->createRequest('/v2/event/' . $event->slug . '/admin/finances'));

        self::assertSame(200, $response->getStatusCode());
        $body = (string)$response->getBody();

        self::assertStringContainsString('rozpad dle ceníku', $body);
        self::assertStringContainsString('se šátkem', $body);
        self::assertStringContainsString('bez šátku', $body);
        self::assertStringContainsString('šátky', $body);
    }

    public function testFinancesPageRedirectsAdminNotEligibleToHandlePayments(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);

        $app = $this->loginAdminForEvent($app, $event, UserRole::IstAdmin);
        $response = $app->handle($this->createRequest('/v2/event/' . $event->slug . '/admin/finances'));

        self::assertSame(302, $response->getStatusCode());
    }

    public function testDashboardLinksToFinances(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);

        $app = $this->loginAdminForEvent($app, $event);
        $response = $app->handle($this->createRequest('/v2/event/' . $event->slug . '/admin/dashboard'));

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('/admin/finances', (string)$response->getBody());
    }
}
