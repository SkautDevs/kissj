<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\Event;
use kissj\Event\EventType\Cej\EventTypeCej;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Participant\Patrol\PatrolParticipantRepository;
use kissj\Participant\Troop\TroopLeaderRepository;
use kissj\Participant\Troop\TroopParticipantRepository;
use kissj\BankPayment\BankPaymentRepository;
use kissj\Payment\PaymentStatus;
use kissj\User\UserRole;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class AdminParticipantScopeTest extends AppTestCase
{
    public function testIstAdminCannotSaveTroopLeaderDetails(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);
        $troopLeader = $this->createParticipantWithRole($app->getContainer(), 'tl', $event);
        $troopLeader->firstName = 'Original';
        $this->getService($app, ParticipantRepository::class)->persist($troopLeader);

        $app = $this->loginAdminForEvent($app, $event, UserRole::IstAdmin);
        $response = $app->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/admin/' . $troopLeader->id . '/changeDetails',
            'POST',
            ['firstName' => 'Hacked'],
        ));

        $this->assertRedirectedToAdminDashboard($app, $event, $response);
        $reloaded = $this->getService($app, ParticipantRepository::class)->getParticipantById($troopLeader->id, $event);
        self::assertSame('Original', $reloaded->firstName);
    }

    public function testIstAdminCanOpenIstDetail(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);
        $ist = $this->createParticipantWithRole($app->getContainer(), 'ist', $event);

        $app = $this->loginAdminForEvent($app, $event, UserRole::IstAdmin);
        $response = $app->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/admin/' . $ist->id . '/mend',
        ));

        self::assertSame(200, $response->getStatusCode());
    }

    public function testIstAdminCannotWriteAdminNoteOfNonIst(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);
        $troopLeader = $this->createParticipantWithRole($app->getContainer(), 'tl', $event);
        $troopLeader->adminNote = 'original note';
        $this->getService($app, ParticipantRepository::class)->persist($troopLeader);

        $app = $this->loginAdminForEvent($app, $event, UserRole::IstAdmin);
        $response = $app->handle($this->createRequest(
            '/v3/event/' . $event->slug . '/admin/' . $troopLeader->id . '/adminNote',
            'POST',
            ['adminNote' => 'hacked note'],
        ));

        $this->assertRedirectedToAdminDashboard($app, $event, $response);
        $reloaded = $this->getService($app, ParticipantRepository::class)->getParticipantById($troopLeader->id, $event);
        self::assertSame('original note', $reloaded->adminNote);
    }

    public function testIstAdminCannotDownloadNonIstFile(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);
        $troopParticipant = $this->createParticipantWithRole($app->getContainer(), 'tp', $event);
        $filename = 'scope-' . uniqid('', true) . '.pdf';
        $troopParticipant->setUploadedFile('parentalConsent', $filename, 'consent.pdf', 'application/pdf');
        $this->getService($app, ParticipantRepository::class)->persist($troopParticipant);

        $app = $this->loginAdminForEvent($app, $event, UserRole::IstAdmin);
        $response = $app->handle($this->createRequest('/v2/event/' . $event->slug . '/showFile/' . $filename));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame($this->urlFor($app, 'getDashboard', $event), $response->getHeaderLine('Location'));
    }

    public function testContingentAdminCannotCancelOtherContingentPayment(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTypedTestEvent($app, 'cej', 'cej-admin-scope-payment');
        $participant = $this->createParticipantWithRole($app->getContainer(), 'ist', $event);
        $participant->contingent = EventTypeCej::CONTINGENT_SLOVAKIA;
        $this->getService($app, ParticipantRepository::class)->persist($participant);
        $payment = $this->createFinancesPayment($app, $event, PaymentStatus::Waiting, '450', participant: $participant);

        $app = $this->loginAdminForEvent($app, $event, UserRole::ContingentAdminCs);
        $response = $app->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/admin/payments/cancelPayment/' . $payment->id,
        ));

        $this->assertRedirectedToAdminDashboard($app, $event, $response);
    }

    public function testContingentAdminReachesBankPairing(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTypedTestEvent($app, 'cej', 'cej-admin-scope-bank');
        $bankPayment = $this->createBankPayment($this->getService($app, BankPaymentRepository::class), $event);

        $app = $this->loginAdminForEvent($app, $event, UserRole::ContingentAdminCs);
        $response = $app->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/admin/payments/auto/setPaymentPaired/' . $bankPayment->id,
            'POST',
            ['notice' => 'paired by test'],
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame($this->urlFor($app, 'admin-show-auto-payments', $event), $response->getHeaderLine('Location'));
    }

    public function testContingentAdminCanOpenOwnPatrolParticipantDetail(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTypedTestEvent($app, 'cej', 'cej-admin-scope-own-pp');
        $patrolParticipant = $this->registerPatrolParticipant($app, $event, EventTypeCej::CONTINGENT_CZECHIA);

        $app = $this->loginAdminForEvent($app, $event, UserRole::ContingentAdminCs);
        $response = $app->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/admin/' . $patrolParticipant->id . '/mend',
        ));

        self::assertSame(200, $response->getStatusCode());
    }

    public function testContingentAdminCannotOpenForeignPatrolParticipantDetail(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTypedTestEvent($app, 'cej', 'cej-admin-scope-foreign-pp');
        $patrolParticipant = $this->registerPatrolParticipant($app, $event, EventTypeCej::CONTINGENT_SLOVAKIA);

        $app = $this->loginAdminForEvent($app, $event, UserRole::ContingentAdminCs);
        $response = $app->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/admin/' . $patrolParticipant->id . '/mend',
        ));

        $this->assertRedirectedToAdminDashboard($app, $event, $response);
    }

    public function testContingentAdminCanOpenOwnTroopParticipantDetail(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTypedTestEvent($app, 'cej', 'cej-admin-scope-own-tp');
        $troopLeader = $this->createParticipantWithRole($app->getContainer(), 'tl', $event);
        $troopLeader->contingent = EventTypeCej::CONTINGENT_CZECHIA;
        $this->getService($app, ParticipantRepository::class)->persist($troopLeader);
        $troopParticipant = $this->getService($app, TroopParticipantRepository::class)->get(
            $this->createParticipantWithRole($app->getContainer(), 'tp', $event)->id,
        );
        $troopParticipant->troopLeader = $this->getService($app, TroopLeaderRepository::class)->get($troopLeader->id);
        $this->getService($app, ParticipantRepository::class)->persist($troopParticipant);

        $app = $this->loginAdminForEvent($app, $event, UserRole::ContingentAdminCs);
        $response = $app->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/admin/' . $troopParticipant->id . '/mend',
        ));

        self::assertSame(200, $response->getStatusCode());
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function registerPatrolParticipant(App $app, Event $event, string $leaderContingent): PatrolParticipant
    {
        $leader = $this->createParticipantWithRole($app->getContainer(), 'pl', $event);
        $leader->contingent = $leaderContingent;
        $this->getService($app, ParticipantRepository::class)->persist($leader);

        $patrolParticipant = new PatrolParticipant();
        $patrolParticipant->patrolLeader = $this->getService($app, PatrolLeaderRepository::class)->get($leader->id);
        $patrolParticipant->firstName = 'Child';
        $patrolParticipant->lastName = 'Scope';
        $this->getService($app, PatrolParticipantRepository::class)->persist($patrolParticipant);

        return $patrolParticipant;
    }
}
