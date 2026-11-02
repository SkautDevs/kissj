<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\Event;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Payment\PaymentStatus;
use kissj\User\UserRepository;
use kissj\User\UserRole;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class AdminLinksVisibilityTest extends AppTestCase
{
    public function testIstAdminDashboardHidesDeniedLinks(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);

        $app = $this->loginAdminForEvent($app, $event, UserRole::IstAdmin);
        $response = $app->handle($this->createRequest('/v2/event/' . $event->slug . '/admin/dashboard'));

        self::assertSame(200, $response->getStatusCode());
        $body = (string)$response->getBody();
        self::assertStringContainsString('/admin/showOpen', $body);
        self::assertStringNotContainsString('/admin/approving', $body);
        self::assertStringNotContainsString('/admin/export/', $body);
        self::assertStringNotContainsString('/admin/roleManagement', $body);
        self::assertStringNotContainsString('/admin/payments', $body);
    }

    public function testIstAdminDetailHidesStatusActions(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);
        $ist = $this->createIst($app, $event, UserStatus::Paid);
        $path = '/admin/' . $ist->id . '/mend';

        $topAdminBody = $this->fetchBodyAs($app, $event, UserRole::Admin, $path);
        self::assertStringContainsString('/changeRole/' . $ist->id . '/cancel', $topAdminBody);
        self::assertStringContainsString('/admin/payments/addNewPayment/' . $ist->id, $topAdminBody);

        $istAdminBody = $this->fetchBodyAs($app, $event, UserRole::IstAdmin, $path);
        self::assertStringNotContainsString('/changeRole/' . $ist->id . '/', $istAdminBody);
        self::assertStringNotContainsString('/admin/payments/addNewPayment/', $istAdminBody);
    }

    public function testIstAdminDetailHidesPaymentActions(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);
        $ist = $this->createIst($app, $event, UserStatus::Approved);
        $this->createFinancesPayment($app, $event, PaymentStatus::Waiting, '450', participant: $ist);
        $path = '/admin/' . $ist->id . '/mend';

        $topAdminBody = $this->fetchBodyAs($app, $event, UserRole::Admin, $path);
        self::assertStringContainsString('/payments/cancelPayment/', $topAdminBody);
        self::assertStringContainsString('/payments/changePrice/', $topAdminBody);
        self::assertStringContainsString('/payments/confirmPayment/', $topAdminBody);

        $istAdminBody = $this->fetchBodyAs($app, $event, UserRole::IstAdmin, $path);
        self::assertStringNotContainsString('/payments/cancelPayment/', $istAdminBody);
        self::assertStringNotContainsString('/payments/changePrice/', $istAdminBody);
        self::assertStringNotContainsString('/payments/confirmPayment/', $istAdminBody);
    }

    public function testIstAdminOpenListHidesChangeRoleLink(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);
        $ist = $this->createIst($app, $event, UserStatus::Open);

        $topAdminBody = $this->fetchBodyAs($app, $event, UserRole::Admin, '/admin/showOpen');
        self::assertStringContainsString('/changeRole/' . $ist->id . '/show', $topAdminBody);

        $istAdminBody = $this->fetchBodyAs($app, $event, UserRole::IstAdmin, '/admin/showOpen');
        self::assertStringNotContainsString('/changeRole/' . $ist->id . '/show', $istAdminBody);
    }

    public function testTopAdminDashboardKeepsLinks(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);

        $app = $this->loginAdminForEvent($app, $event, UserRole::Admin);
        $response = $app->handle($this->createRequest('/v2/event/' . $event->slug . '/admin/dashboard'));

        self::assertSame(200, $response->getStatusCode());
        $body = (string)$response->getBody();
        self::assertStringContainsString('/admin/approving', $body);
        self::assertStringContainsString('/admin/roleManagement', $body);
        self::assertStringContainsString('/admin/payments', $body);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createIst(App $app, Event $event, UserStatus $status): Participant
    {
        $participant = $this->createParticipantWithRole($app->getContainer(), 'ist', $event);
        $participant->firstName = 'Linkcheck';
        $participant->lastName = 'Ist';
        $this->getService($app, ParticipantRepository::class)->persist($participant);
        $istUser = $participant->getUserButNotNull();
        $istUser->status = $status;
        $this->getService($app, UserRepository::class)->persist($istUser);

        return $participant;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function fetchBodyAs(App $app, Event $event, UserRole $role, string $path): string
    {
        $loggedInApp = $this->loginAdminForEvent($app, $event, $role);
        $response = $loggedInApp->handle($this->createRequest('/v2/event/' . $event->slug . $path));

        self::assertSame(200, $response->getStatusCode());

        return (string)$response->getBody();
    }
}
