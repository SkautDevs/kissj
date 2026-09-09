<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\Participant;
use kissj\Payment\PaymentStatus;
use kissj\User\UserRepository;
use kissj\User\UserRole;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class FinancesAdminPageTest extends AppTestCase
{
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

        $this->createFinancesPayment($app, $event, PaymentStatus::Paid, '450', '2026-05-10');
        $this->createFinancesPayment($app, $event, PaymentStatus::Paid, '600', '2026-05-20');
        $this->createFinancesPayment($app, $event, PaymentStatus::Waiting, '750');

        $app = $this->loginAdminForEvent($app, $event);
        $response = $app->handle($this->createRequest('/v2/event/' . $event->slug . '/admin/finances'));

        self::assertSame(200, $response->getStatusCode());
        $body = (string)$response->getBody();

        self::assertStringContainsString('přijaté platby po měsících', $body);
        self::assertStringContainsString('květen 2026', $body);
        self::assertStringNotContainsString('2026-05', $body);
        self::assertStringContainsString('1050', $body);
        self::assertStringContainsString('očekávané peníze z nezaplacených plateb', $body);
        self::assertStringContainsString('750', $body);
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

        $this->createFinancesPayment($app, $event, PaymentStatus::Paid, '600', '2026-05-12', scarf: Participant::SCARF_YES);
        $this->createFinancesPayment($app, $event, PaymentStatus::Waiting, '750', scarf: Participant::SCARF_YES);

        $app = $this->loginAdminForEvent($app, $event);
        $response = $app->handle($this->createRequest('/v2/event/' . $event->slug . '/admin/finances'));

        self::assertSame(200, $response->getStatusCode());
        $body = (string)$response->getBody();

        self::assertStringContainsString('rozpad dle ceníku', $body);
        self::assertStringContainsString('se šátkem', $body);
        self::assertStringContainsString('bez šátku', $body);
        self::assertStringContainsString('šátky', $body);
    }

    public function testFinancesPageShowsSingleTierWithoutScarvesForDefaultEvent(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);

        $event = $eventRepository->findBySlug('test-event-slug');
        self::assertNotNull($event);
        $event->defaultPrice = 450;
        $eventRepository->persist($event);
        $event = $eventRepository->get($event->id);

        $this->createFinancesPayment($app, $event, PaymentStatus::Paid, '450', '2026-05-10');
        $this->createFinancesPayment($app, $event, PaymentStatus::Waiting, '999');

        $app = $this->loginAdminForEvent($app, $event);
        $response = $app->handle($this->createRequest('/v2/event/' . $event->slug . '/admin/finances'));

        self::assertSame(200, $response->getStatusCode());
        $body = (string)$response->getBody();

        self::assertStringContainsString('rozpad dle ceníku', $body);
        self::assertStringContainsString('450', $body);
        self::assertStringContainsString('ostatní částky', $body);
        self::assertStringNotContainsString('se šátkem', $body);
        self::assertStringNotContainsString('šátky', $body);
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
