<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Device\DevicePairingAttemptRepository;
use kissj\Device\DevicePairingRefusal;
use kissj\Device\DevicePairingRepository;
use kissj\Device\DevicePairingService;
use kissj\Device\DevicePairingStatus;
use kissj\Device\DeviceSessionRepository;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Middleware\DevicePairingCsrfMiddleware;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Settings\EnvSettings;
use kissj\Telemetry\Metrics;
use kissj\User\LoginToken;
use kissj\User\LoginTokenRepository;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserRole;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Monolog\Level;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Tests\AppTestCase;

class DevicePairingWebTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-event-slug';
    private const string PAIRING_PATH = '/v2/event/test-event-slug/participant/pairing';
    private const string DASHBOARD_PATH = '/v2/event/test-event-slug/participant/dashboard';
    private const string DASHBOARD_RETRY_PATH = '/v2/event/test-event-slug/participant/dashboard?pairingRetry=1';
    private const string ADMIN_PROGRAMMES_PATH = '/v2/event/test-event-slug/admin/programmes';
    private const string PURGE_PATH = '/v2/event/test-event-slug/admin/programmes/pairings/purge';

    public function testDashboardWithoutDeviceShowsFormAndAppLink(): void
    {
        $this->setEnv('EVENT_APP_URL', 'https://app.example.org/');
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $participant = $this->createPaidIstParticipant($app);
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        $body = (string)$app->handle($this->createRequest(self::DASHBOARD_PATH))->getBody();

        self::assertStringContainsString('href="https://app.example.org/test-event-slug/"', $body);
        self::assertStringContainsString($this->translate($app, 'device.pairing.cardTitle'), $body);
        self::assertStringContainsString(
            '<label for="code">' . explode('%app%', $this->translate($app, 'device.pairing.cardText'))[0],
            $body,
        );
        self::assertStringContainsString('action="/v2/event/test-event-slug/participant/pairing"', $body);
        self::assertStringNotContainsString('<details', $body);
    }

    public function testDashboardAfterPairingShowsConnectedCountAndCollapsedForm(): void
    {
        $this->setEnv('EVENT_APP_URL', 'https://app.example.org/');
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $pairing = $this->createDevicePairing($app, $this->testEvent($app), '390157');
        $participant = $this->createPaidIstParticipant($app);
        self::assertNull($this->getService($app, DevicePairingService::class)
            ->approveEnteredCode('390157', $pairing->event, $participant));
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        $body = (string)$app->handle($this->createRequest(self::DASHBOARD_PATH))->getBody();

        self::assertStringContainsString(
            str_replace('%count%', '1', $this->translate($app, 'device.pairing.connectedCount')),
            $body,
        );
        self::assertStringContainsString($this->translate($app, 'device.pairing.openApp'), $body);
        self::assertStringContainsString('href="https://app.example.org/test-event-slug/"', $body);
        self::assertStringContainsString('<details', $body);
        self::assertStringContainsString($this->translate($app, 'device.pairing.connectAnother'), $body);
        self::assertStringContainsString('action="/v2/event/test-event-slug/participant/pairing"', $body);
        self::assertStringContainsString('id="devicePairing"', $body);
    }

    public function testFailedCodeKeepsConnectAnotherFormOpen(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $pairing = $this->createDevicePairing($app, $this->testEvent($app), '390157');
        $participant = $this->createPaidIstParticipant($app);
        self::assertNull($this->getService($app, DevicePairingService::class)
            ->approveEnteredCode('390157', $pairing->event, $participant));
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        $response = $this->post($app, self::PAIRING_PATH, ['code' => '000000']);

        $this->assertRedirectsToDashboardWithFormOpen($response);
        $retryRequest = $this->createRequest(self::DASHBOARD_PATH)->withQueryParams(['pairingRetry' => '1']);
        $retryBody = (string)$app->handle($retryRequest)->getBody();
        self::assertStringContainsString('<details open>', $retryBody);
        $plainBody = (string)$app->handle($this->createRequest(self::DASHBOARD_PATH))->getBody();
        self::assertStringContainsString('<details>', $plainBody);
    }

    public function testDashboardWithoutEventAppUrlHasNoAppLink(): void
    {
        $this->setEnv('EVENT_APP_URL', '');
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $pairing = $this->createDevicePairing($app, $this->testEvent($app), '390157');
        $participant = $this->createPaidIstParticipant($app);
        $notConnected = $this->createPaidIstParticipant($app);
        self::assertNull($this->getService($app, DevicePairingService::class)
            ->approveEnteredCode('390157', $pairing->event, $participant));

        $connectedApp = $this->loginAs($app, $participant->getUserButNotNull());
        $connectedBody = (string)$connectedApp->handle($this->createRequest(self::DASHBOARD_PATH))->getBody();
        self::assertStringContainsString(
            str_replace('%count%', '1', $this->translate($connectedApp, 'device.pairing.connectedCount')),
            $connectedBody,
        );
        self::assertStringNotContainsString(
            $this->translate($connectedApp, 'device.pairing.openApp'),
            $connectedBody,
        );

        $freshApp = $this->loginAs($this->getTestApp(false), $notConnected->getUserButNotNull());
        $freshBody = (string)$freshApp->handle($this->createRequest(self::DASHBOARD_PATH))->getBody();
        self::assertStringContainsString($this->translate($freshApp, 'device.pairing.appName'), $freshBody);
        self::assertStringNotContainsString('href="https://app.', $freshBody);
        self::assertStringNotContainsString('target="_blank" rel="noopener"', $freshBody);
    }

    public function testCardAndPairingOnlyWithEventKey(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, null);
        $participant = $this->createPaidIstParticipant($app);
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        $dashboard = $app->handle($this->createRequest(self::DASHBOARD_PATH));
        self::assertSame(200, $dashboard->getStatusCode());
        self::assertStringNotContainsString('participant/pairing', (string)$dashboard->getBody());

        try {
            $app->handle($this->createRequest(self::PAIRING_PATH, 'POST', ['code' => '123456']));
            self::fail('POST ' . self::PAIRING_PATH . ' is reachable without the event key');
        } catch (HttpNotFoundException $exception) {
            self::assertSame(404, $exception->getCode());
        }

        $this->setEventKey($app, 'pairing-web-event-key');
        $app = $this->getTestApp(false);
        $pairing = $this->createDevicePairing($app, $this->testEvent($app), '390157');

        $dashboardWithKey = $app->handle($this->createRequest(self::DASHBOARD_PATH));
        self::assertSame(200, $dashboardWithKey->getStatusCode());
        $body = (string)$dashboardWithKey->getBody();
        self::assertStringContainsString('action="/v2/event/test-event-slug/participant/pairing"', $body);
        self::assertStringContainsString('class="form-control"', $body);
        self::assertStringContainsString('pattern=" *\d{3}[ \-]*\d{3} *"', $body);
        self::assertStringContainsString('name="csrfName"', $body);

        self::assertSame(1, preg_match('/name="csrfName" value="([^"]+)"/', $body, $csrfName));
        self::assertSame(1, preg_match('/name="csrfValue" value="([^"]+)"/', $body, $csrfValue));

        $response = $app->handle($this->createRequest(self::PAIRING_PATH, 'POST', [
            'code' => '390157',
            'csrfName' => $csrfName[1],
            'csrfValue' => $csrfValue[1],
        ]));

        $this->assertRedirectsToDashboard($response);
        self::assertContains($this->translate($app, 'flash.success.devicePaired'), $this->flashed($app));
        $stored = $this->getService($app, DevicePairingRepository::class)->get($pairing->id);
        self::assertSame('approved', $stored->status->value);

        $this->expectException(HttpMethodNotAllowedException::class);
        $app->handle($this->createRequest(self::PAIRING_PATH));
    }

    public function testCancelledParticipantGetsNoCardAndPairingIs404(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $participant = $this->createPaidIstParticipant($app);
        $user = $participant->getUserButNotNull();
        $user->status = UserStatus::Cancelled;
        $this->getService($app, UserRepository::class)->persist($user);
        $app = $this->loginAs($app, $user);

        $dashboard = $app->handle($this->createRequest(self::DASHBOARD_PATH));
        self::assertSame(200, $dashboard->getStatusCode());
        self::assertStringNotContainsString('participant/pairing', (string)$dashboard->getBody());

        $this->expectException(HttpNotFoundException::class);
        $app->handle($this->createRequest(self::PAIRING_PATH, 'POST', ['code' => '123456']));
    }

    public function testWrongCodeFlashesInvalid(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $participant = $this->createPaidIstParticipant($app);
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        $response = $this->post($app, self::PAIRING_PATH, ['code' => '000000']);

        $this->assertRedirectsToDashboardWithFormOpen($response);
        self::assertContains($this->translate($app, 'flash.warning.devicePairingCodeInvalid'), $this->flashed($app));
        self::assertSame(1, $this->countAttempts($app, $participant));
    }

    public function testCodeCheckLocksParticipantRow(): void
    {
        $app = $this->getTestApp();
        $participant = $this->createPaidIstParticipant($app);
        $participantRepository = $this->createMock(ParticipantRepository::class);
        $participantRepository->expects(self::once())->method('lock')->with($participant);
        $service = new DevicePairingService(
            $this->getService($app, DevicePairingRepository::class),
            $this->getService($app, DevicePairingAttemptRepository::class),
            $this->getService($app, DeviceSessionRepository::class),
            $participantRepository,
            $this->getService($app, LoggerInterface::class),
            $this->getService($app, Metrics::class),
            $this->getService($app, EnvSettings::class),
        );

        $service->approveEnteredCode('000000', $participant->getUserButNotNull()->event, $participant);
    }

    public function testEnteredCodeIsNormalisedAndGarbageCounts(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $repository = $this->getService($app, DevicePairingRepository::class);
        $dashed = $this->createDevicePairing($app, $this->testEvent($app), '483291');
        $spaced = $this->createDevicePairing($app, $this->testEvent($app), '483292');
        $participant = $this->createPaidIstParticipant($app);
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        $this->assertRedirectsToDashboard($this->post($app, self::PAIRING_PATH, ['code' => '483-291']));
        $this->assertRedirectsToDashboard($this->post($app, self::PAIRING_PATH, ['code' => '483 292']));
        self::assertSame('approved', $repository->get($dashed->id)->status->value);
        self::assertSame('approved', $repository->get($spaced->id)->status->value);
        self::assertSame(0, $this->countAttempts($app, $participant));

        foreach (['48329', '48a291'] as $index => $entered) {
            $response = $this->post($app, self::PAIRING_PATH, ['code' => $entered]);
            $this->assertRedirectsToDashboardWithFormOpen($response);
            self::assertSame($index + 1, $this->countAttempts($app, $participant), $entered);
        }
    }

    public function testCodeOfAnotherEventIsInvalid(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $otherEvent = $this->getSmallTestEvent($this->getService($app, EventRepository::class));
        $this->createDevicePairing($app, $otherEvent, '715302');
        $participant = $this->createPaidIstParticipant($app);
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        $response = $this->post($app, self::PAIRING_PATH, ['code' => '715302']);

        $this->assertRedirectsToDashboardWithFormOpen($response);
        self::assertContains($this->translate($app, 'flash.warning.devicePairingCodeInvalid'), $this->flashed($app));
        self::assertSame(1, $this->countAttempts($app, $participant));
    }

    public function testApproveBindsParticipant(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $pairing = $this->createDevicePairing($app, $this->testEvent($app), '390157');
        $participant = $this->createPaidIstParticipant($app);
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        $response = $this->post($app, self::PAIRING_PATH, ['code' => '390157']);

        $this->assertRedirectsToDashboard($response);
        self::assertContains($this->translate($app, 'flash.success.devicePaired'), $this->flashed($app));
        $stored = $this->getService($app, DevicePairingRepository::class)->get($pairing->id);
        self::assertSame('approved', $stored->status->value);
        self::assertSame($participant->id, $stored->participant?->id);
    }

    public function testSubmitTwiceIsRefused(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $pairing = $this->createDevicePairing($app, $this->testEvent($app), '572940');
        $participant = $this->createPaidIstParticipant($app);
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        $this->post($app, self::PAIRING_PATH, ['code' => '572940']);
        $this->flashed($app);
        $response = $this->post($app, self::PAIRING_PATH, ['code' => '572940']);

        $this->assertRedirectsToDashboardWithFormOpen($response);
        self::assertContains($this->translate($app, 'flash.warning.devicePairingCodeUsed'), $this->flashed($app));
        $stored = $this->getService($app, DevicePairingRepository::class)->get($pairing->id);
        self::assertSame('approved', $stored->status->value);
        self::assertSame(0, $this->countAttempts($app, $participant));
    }

    public function testSubmitOfCodeReplacedByNewStartRecordsNoAttempt(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $event = $this->testEvent($app);
        $pairing = $this->createDevicePairing($app, $event, '618304');
        $participant = $this->createPaidIstParticipant($app);
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        $this->getService($app, DevicePairingService::class)->start($event, $pairing->deviceId);
        $response = $this->post($app, self::PAIRING_PATH, ['code' => '618304']);

        $this->assertRedirectsToDashboardWithFormOpen($response);
        self::assertContains($this->translate($app, 'flash.warning.devicePairingCodeUsed'), $this->flashed($app));
        $stored = $this->getService($app, DevicePairingRepository::class)->get($pairing->id);
        self::assertSame('replaced', $stored->status->value);
        self::assertNull($stored->participant);
        self::assertSame(0, $this->countAttempts($app, $participant));
    }

    public function testSecondParticipantEnteringApprovedCodeIsRefusedAndPairingStaysWithFirst(): void
    {
        $app = $this->getTestApp();
        $pairing = $this->createDevicePairing($app, $this->testEvent($app), '407215');
        $participant = $this->createPaidIstParticipant($app);
        $intruder = $this->createPaidIstParticipant($app);
        $service = $this->getService($app, DevicePairingService::class);

        self::assertNull($service->approveEnteredCode('407215', $pairing->event, $participant));

        self::assertSame(
            DevicePairingRefusal::CodeAlreadyUsed,
            $service->approveEnteredCode('407215', $pairing->event, $intruder),
        );
        $stored = $this->getService($app, DevicePairingRepository::class)->get($pairing->id);
        self::assertSame('approved', $stored->status->value);
        self::assertSame($participant->id, $stored->participant?->id);
    }

    public function testCodePastedWithSurroundingSpacesIsApproved(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $pairing = $this->createDevicePairing($app, $this->testEvent($app), '483293');
        $participant = $this->createPaidIstParticipant($app);
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        $response = $this->post($app, self::PAIRING_PATH, ['code' => ' 483 293 ']);

        $this->assertRedirectsToDashboard($response);
        self::assertContains($this->translate($app, 'flash.success.devicePaired'), $this->flashed($app));
        $stored = $this->getService($app, DevicePairingRepository::class)->get($pairing->id);
        self::assertSame('approved', $stored->status->value);
    }

    public function testResubmitAfterPhoneCollectedPairingFlashesUsedAndRecordsNoAttempt(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $event = $this->testEvent($app);
        $pairing = $this->createDevicePairing($app, $event, '730915');
        $participant = $this->createPaidIstParticipant($app);
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        $this->post($app, self::PAIRING_PATH, ['code' => '730915']);
        $this->flashed($app);
        $this->getService($app, DevicePairingService::class)->poll($pairing->deviceCode, $event);
        $response = $this->post($app, self::PAIRING_PATH, ['code' => '730915']);

        $this->assertRedirectsToDashboardWithFormOpen($response);
        self::assertContains($this->translate($app, 'flash.warning.devicePairingCodeUsed'), $this->flashed($app));
        $stored = $this->getService($app, DevicePairingRepository::class)->get($pairing->id);
        self::assertSame('consumed', $stored->status->value);
        self::assertSame(0, $this->countAttempts($app, $participant));
    }

    public function testUsedCodeFlashesUsed(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $this->createDevicePairing($app, $this->testEvent($app), '730916', status: DevicePairingStatus::Consumed);
        $participant = $this->createPaidIstParticipant($app);
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        $response = $this->post($app, self::PAIRING_PATH, ['code' => '730916']);

        $this->assertRedirectsToDashboardWithFormOpen($response);
        self::assertContains($this->translate($app, 'flash.warning.devicePairingCodeUsed'), $this->flashed($app));
        self::assertSame(0, $this->countAttempts($app, $participant));
    }

    public function testPendingPairingWinsOverUsedPairingWithSameCode(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $event = $this->testEvent($app);
        $this->createDevicePairing($app, $event, '123456', status: DevicePairingStatus::Consumed);
        $this->createDevicePairing($app, $event, '123456');
        $participant = $this->createPaidIstParticipant($app);
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        $response = $this->post($app, self::PAIRING_PATH, ['code' => '123456']);

        $this->assertRedirectsToDashboard($response);
        self::assertContains($this->translate($app, 'flash.success.devicePaired'), $this->flashed($app));
        self::assertSame(0, $this->countAttempts($app, $participant));
    }

    public function testUsedCodeOlderThanOneHourCountsAsWrongGuess(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $old = $this->createDevicePairing(
            $app,
            $this->testEvent($app),
            '730917',
            status: DevicePairingStatus::Consumed,
        );
        $repository = $this->getService($app, DevicePairingRepository::class);
        $old->createdAt = DateTimeUtils::getDateTime('-61 minutes');
        $repository->persist($old);
        $participant = $this->createPaidIstParticipant($app);
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        $response = $this->post($app, self::PAIRING_PATH, ['code' => '730917']);

        $this->assertRedirectsToDashboardWithFormOpen($response);
        self::assertContains($this->translate($app, 'flash.warning.devicePairingCodeInvalid'), $this->flashed($app));
        self::assertSame(1, $this->countAttempts($app, $participant));
    }

    public function testLockedOutParticipantGetsTooManyAttemptsForUsedCode(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $this->createDevicePairing($app, $this->testEvent($app), '730918', status: DevicePairingStatus::Replaced);
        $participant = $this->createPaidIstParticipant($app);
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        $this->expectLog(Level::Warning, 'Device pairing refused, participant locked out');
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->post($app, self::PAIRING_PATH, ['code' => '000000']);
        }
        $this->flashed($app);

        $response = $this->post($app, self::PAIRING_PATH, ['code' => '730918']);

        $this->assertRedirectsToDashboardWithFormOpen($response);
        self::assertContains(
            $this->translate($app, 'flash.warning.devicePairingTooManyAttempts'),
            $this->flashed($app),
        );
    }

    public function testSubmitWithoutCsrfTokenIsRejected(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $pairing = $this->createDevicePairing($app, $this->testEvent($app), '618374');
        $participant = $this->createPaidIstParticipant($app);
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        $response = $app->handle($this->createRequest(
            self::PAIRING_PATH,
            'POST',
            ['code' => '618374'],
        ));

        $this->assertRedirectsToDashboard($response);
        self::assertContains($this->translate($app, 'flash.error.formExpired'), $this->flashed($app));
        $stored = $this->getService($app, DevicePairingRepository::class)->get($pairing->id);
        self::assertSame('pending', $stored->status->value);
    }

    public function testLockoutAfterTenFailures(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $pairing = $this->createDevicePairing($app, $this->testEvent($app), '935126');
        $participant = $this->createPaidIstParticipant($app);
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        $this->expectLog(Level::Warning, 'Device pairing refused, participant locked out');
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->post($app, self::PAIRING_PATH, ['code' => '000000']);
        }
        $this->flashed($app);

        $lockedOut = $this->post($app, self::PAIRING_PATH, ['code' => '935126']);

        $this->assertRedirectsToDashboardWithFormOpen($lockedOut);
        self::assertContains(
            $this->translate($app, 'flash.warning.devicePairingTooManyAttempts'),
            $this->flashed($app),
        );
        $repository = $this->getService($app, DevicePairingRepository::class);
        self::assertSame('pending', $repository->get($pairing->id)->status->value);

        $attemptRepository = $this->getService($app, DevicePairingAttemptRepository::class);
        foreach ($attemptRepository->findBy(['participant' => $participant]) as $attemptRow) {
            $attemptRow->createdAt = DateTimeUtils::getDateTime('-2 hours');
            $attemptRepository->persist($attemptRow);
        }

        $response = $this->post($app, self::PAIRING_PATH, ['code' => '935126']);

        $this->assertRedirectsToDashboard($response);
        self::assertSame('approved', $repository->get($pairing->id)->status->value);
    }

    public function testAdminSeesPendingCountAndPurgesOnlyPendingPairingsOfEvent(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $event = $this->testEvent($app);
        $repository = $this->getService($app, DevicePairingRepository::class);
        $pending = $this->createDevicePairing($app, $event, '111111');
        $expiredPending = $this->createDevicePairing($app, $event, '222222');
        $expiredPending->expiresAt = DateTimeUtils::getDateTime('-1 minute');
        $repository->persist($expiredPending);
        $approved = $this->createDevicePairing($app, $event, '333333');
        $approved->status = DevicePairingStatus::Approved;
        $repository->persist($approved);
        $foreign = $this->createDevicePairing(
            $app,
            $this->getTypedTestEvent($app, 'default', 'device-pairing-purge-other-event'),
            '444444',
        );
        $app = $this->loginAs($app, $this->registerAdmin($app));

        $overview = (string)$app->handle($this->createRequest(self::ADMIN_PROGRAMMES_PATH))->getBody();
        self::assertStringContainsString('1 / 1000', $overview);
        self::assertStringContainsString('action="' . self::PURGE_PATH . '"', $overview);

        $this->expectLog(Level::Warning, 'Pending device pairings purged by admin');
        $response = $app->handle($this->createRequest(self::PURGE_PATH, 'POST'));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame(self::ADMIN_PROGRAMMES_PATH, $response->getHeaderLine('Location'));
        self::assertContains(
            str_replace('%count%', '2', $this->translate($app, 'flash.success.devicePairingsPurged')),
            $this->flashed($app),
        );
        self::assertNull($repository->findOneBy(['id' => $pending->id]));
        self::assertNull($repository->findOneBy(['id' => $expiredPending->id]));
        self::assertSame('approved', $repository->get($approved->id)->status->value);
        self::assertSame('pending', $repository->get($foreign->id)->status->value);
    }

    public function testPurgeWithoutEventKeyIsNotFound(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, null);
        $app = $this->loginAs($app, $this->registerAdmin($app));

        $overview = $app->handle($this->createRequest(self::ADMIN_PROGRAMMES_PATH));
        self::assertSame(200, $overview->getStatusCode());
        self::assertStringNotContainsString('pairings/purge', (string)$overview->getBody());

        $this->expectException(HttpNotFoundException::class);
        $app->handle($this->createRequest(self::PURGE_PATH, 'POST'));
    }

    private function assertRedirectsToDashboard(ResponseInterface $response): void
    {
        self::assertSame(302, $response->getStatusCode());
        self::assertSame(self::DASHBOARD_PATH, $response->getHeaderLine('Location'));
    }

    private function assertRedirectsToDashboardWithFormOpen(ResponseInterface $response): void
    {
        self::assertSame(302, $response->getStatusCode());
        self::assertSame(self::DASHBOARD_RETRY_PATH, $response->getHeaderLine('Location'));
    }

    /**
     * @param App<ContainerInterface> $app
     * @param array<string, string> $body
     */
    private function post(App $app, string $path, array $body): ResponseInterface
    {
        /** @var array<string, string> $csrf */
        $csrf = $this->getService($app, DevicePairingCsrfMiddleware::class)->generateToken();

        return $app->handle($this->createRequest($path, 'POST', $body + $csrf));
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function countAttempts(App $app, Participant $participant): int
    {
        return count($this->getService($app, DevicePairingAttemptRepository::class)->findBy([
            'participant' => $participant,
        ]));
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function testEvent(App $app): Event
    {
        return $this->getService($app, EventRepository::class)->getOneBy(['slug' => self::TEST_EVENT_SLUG]);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function setEventKey(App $app, ?string $key): void
    {
        $this->mutateEventForTest($app->getContainer(), $this->testEvent($app), ['apiKeyEvent' => $key]);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createPaidIstParticipant(App $app): Participant
    {
        $userService = $this->getService($app, UserService::class);
        $user = $userService->registerEmailUser(
            'pairing-web-' . bin2hex(random_bytes(6)) . '@example.com',
            $this->testEvent($app),
        );
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $userRepository = $this->getService($app, UserRepository::class);
        $user = $userRepository->get($user->id);
        $user->status = UserStatus::Paid;
        $userRepository->persist($user);

        return $participant;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function registerAdmin(App $app): User
    {
        $user = $this->getService($app, UserService::class)->registerEmailUser(
            'pairing-admin-' . bin2hex(random_bytes(6)) . '@example.com',
            $this->testEvent($app),
        );
        $user->role = UserRole::Admin;
        $this->getService($app, UserRepository::class)->persist($user);

        return $user;
    }

    /**
     * @param App<ContainerInterface> $app
     * @return App<ContainerInterface>
     */
    private function loginAs(App $app, User $user): App
    {
        $loginToken = new LoginToken();
        $loginToken->token = bin2hex(random_bytes(16));
        $loginToken->user = $user;
        $loginToken->used = false;
        $this->getService($app, LoginTokenRepository::class)->persist($loginToken);

        $response = $app->handle($this->createRequest(
            '/v2/event/' . self::TEST_EVENT_SLUG . '/tryLogin/' . $loginToken->token,
        ));
        self::assertSame(302, $response->getStatusCode());

        return $this->getTestApp(false);
    }
}
