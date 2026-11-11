<?php

declare(strict_types=1);

namespace Tests\Functional;

use DateTimeImmutable;
use DateTimeZone;
use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Middleware\DevicePairingCsrfMiddleware;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Programme\Device\DevicePairingAttemptRepository;
use kissj\Programme\Device\DevicePairingRepository;
use kissj\Programme\Device\DevicePairingService;
use kissj\Programme\Device\DevicePairingStatus;
use kissj\Programme\Device\DeviceSessionRepository;
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
use Slim\Exception\HttpNotFoundException;
use Tests\AppTestCase;

class DevicePairingWebTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-event-slug';
    private const string PAIRING_PATH = '/v2/event/test-event-slug/participant/pairing';
    private const string CONFIRM_PATH = '/v2/event/test-event-slug/participant/pairing/confirm';
    private const string DASHBOARD_PATH = '/v2/event/test-event-slug/participant/dashboard';
    private const string ADMIN_PROGRAMMES_PATH = '/v2/event/test-event-slug/admin/programmes';
    private const string PURGE_PATH = '/v2/event/test-event-slug/admin/programmes/pairings/purge';

    public function testPageAndCardOnlyWithEventKey(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, null);
        $participant = $this->createPaidIstParticipant($app);
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        $dashboard = $app->handle($this->createRequest(self::DASHBOARD_PATH));
        self::assertSame(200, $dashboard->getStatusCode());
        self::assertStringNotContainsString('participant/pairing', (string)$dashboard->getBody());

        foreach ([[self::PAIRING_PATH, 'GET'], [self::PAIRING_PATH, 'POST'], [self::CONFIRM_PATH, 'POST']] as $route) {
            try {
                $app->handle($this->createRequest($route[0], $route[1], ['code' => '123456']));
                self::fail($route[1] . ' ' . $route[0] . ' is reachable without the event key');
            } catch (HttpNotFoundException $exception) {
                self::assertSame(404, $exception->getCode());
            }
        }

        $this->setEventKey($app, 'pairing-web-event-key');
        $app = $this->getTestApp(false);

        $dashboardWithKey = $app->handle($this->createRequest(self::DASHBOARD_PATH));
        self::assertSame(200, $dashboardWithKey->getStatusCode());
        self::assertStringContainsString(
            'href="/v2/event/test-event-slug/participant/pairing"',
            (string)$dashboardWithKey->getBody(),
        );

        $form = $app->handle($this->createRequest(self::PAIRING_PATH));
        self::assertSame(200, $form->getStatusCode());
        self::assertStringContainsString('name="code"', (string)$form->getBody());
    }

    public function testCancelledParticipantGetsNoPageNorCard(): void
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

        foreach ([[self::PAIRING_PATH, 'GET'], [self::PAIRING_PATH, 'POST'], [self::CONFIRM_PATH, 'POST']] as $route) {
            try {
                $app->handle($this->createRequest($route[0], $route[1], ['code' => '123456']));
                self::fail($route[1] . ' ' . $route[0] . ' is reachable by a cancelled participant');
            } catch (HttpNotFoundException $exception) {
                self::assertSame(404, $exception->getCode());
            }
        }
    }

    public function testWrongCodeFlashesInvalid(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $participant = $this->createPaidIstParticipant($app);
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        $response = $this->post($app, self::PAIRING_PATH, ['code' => '000000']);

        $this->assertRedirectsToForm($response);
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
        );

        $service->findPendingForParticipant('000000', $participant->getUserButNotNull()->event, $participant);
    }

    public function testEnteredCodeIsNormalisedAndGarbageCounts(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $this->createDevicePairing($app, $this->testEvent($app), '483291');
        $participant = $this->createPaidIstParticipant($app);
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        foreach (['483-291', '483 291'] as $entered) {
            $response = $this->post($app, self::PAIRING_PATH, ['code' => $entered]);
            self::assertSame(200, $response->getStatusCode(), $entered);
            self::assertStringContainsString('value="approve"', (string)$response->getBody(), $entered);
        }
        self::assertSame(0, $this->countAttempts($app, $participant));

        foreach (['48329', '48a291'] as $index => $entered) {
            $response = $this->post($app, self::PAIRING_PATH, ['code' => $entered]);
            $this->assertRedirectsToForm($response);
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

        $this->assertRedirectsToForm($response);
        self::assertContains($this->translate($app, 'flash.warning.devicePairingCodeInvalid'), $this->flashed($app));
        self::assertSame(1, $this->countAttempts($app, $participant));
    }

    public function testConfirmationShowsAppEventAndTime(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $event = $this->testEvent($app);
        $pairing = $this->createDevicePairing($app, $event, '264810');
        $participant = $this->createPaidIstParticipant($app);
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        $response = $this->post($app, self::PAIRING_PATH, ['code' => '264810']);

        self::assertSame(200, $response->getStatusCode());
        $body = (string)$response->getBody();
        $createdAt = $this->getService($app, DevicePairingRepository::class)->get($pairing->id)->createdAt;
        self::assertNotNull($createdAt);
        $localCreatedAt = DateTimeImmutable::createFromInterface($createdAt)
            ->setTimezone(new DateTimeZone(date_default_timezone_get()));
        self::assertStringContainsString('eventApp', $body);
        self::assertStringContainsString($event->readableName, $body);
        self::assertStringContainsString($localCreatedAt->format('j. n. Y H:i'), $body);
        self::assertMatchesRegularExpression('/name="code" type="hidden" value="264810"/', $body);
        self::assertStringContainsString('name="decision" value="approve"', $body);
        self::assertStringContainsString('name="decision" value="deny"', $body);
        self::assertMatchesRegularExpression('/name="csrfValue" value="[^"]+"/', $body);
    }

    public function testApproveBindsParticipant(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $pairing = $this->createDevicePairing($app, $this->testEvent($app), '390157');
        $participant = $this->createPaidIstParticipant($app);
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        $response = $this->post($app, self::CONFIRM_PATH, ['code' => '390157', 'decision' => 'approve']);

        $this->assertRedirectsToDashboard($response);
        self::assertContains($this->translate($app, 'flash.success.devicePaired'), $this->flashed($app));
        $stored = $this->getService($app, DevicePairingRepository::class)->get($pairing->id);
        self::assertSame('approved', $stored->status->value);
        self::assertSame($participant->id, $stored->participant?->id);
    }

    public function testDenyMarksDenied(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $pairing = $this->createDevicePairing($app, $this->testEvent($app), '846203');
        $participant = $this->createPaidIstParticipant($app);
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        $response = $this->post($app, self::CONFIRM_PATH, ['code' => '846203', 'decision' => 'deny']);

        $this->assertRedirectsToDashboard($response);
        self::assertContains($this->translate($app, 'flash.info.devicePairingDenied'), $this->flashed($app));
        $stored = $this->getService($app, DevicePairingRepository::class)->get($pairing->id);
        self::assertSame('denied', $stored->status->value);
        self::assertNull($stored->participant);
    }

    public function testConfirmTwiceIsRefused(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $pairing = $this->createDevicePairing($app, $this->testEvent($app), '572940');
        $participant = $this->createPaidIstParticipant($app);
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        $this->post($app, self::CONFIRM_PATH, ['code' => '572940', 'decision' => 'approve']);
        $this->flashed($app);
        $response = $this->post($app, self::CONFIRM_PATH, ['code' => '572940', 'decision' => 'approve']);

        $this->assertRedirectsToForm($response);
        self::assertContains($this->translate($app, 'flash.warning.devicePairingCodeUsed'), $this->flashed($app));
        $stored = $this->getService($app, DevicePairingRepository::class)->get($pairing->id);
        self::assertSame('approved', $stored->status->value);
        self::assertSame(0, $this->countAttempts($app, $participant));
    }

    public function testConfirmOfCodeReplacedByNewStartRecordsNoAttempt(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $event = $this->testEvent($app);
        $pairing = $this->createDevicePairing($app, $event, '618304');
        $participant = $this->createPaidIstParticipant($app);
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        $this->getService($app, DevicePairingService::class)->start($event, $pairing->deviceId);
        $response = $this->post($app, self::CONFIRM_PATH, ['code' => '618304', 'decision' => 'approve']);

        $this->assertRedirectsToForm($response);
        self::assertContains($this->translate($app, 'flash.warning.devicePairingCodeUsed'), $this->flashed($app));
        $stored = $this->getService($app, DevicePairingRepository::class)->get($pairing->id);
        self::assertSame('replaced', $stored->status->value);
        self::assertNull($stored->participant);
        self::assertSame(0, $this->countAttempts($app, $participant));
    }

    public function testStaleDecisionLeavesDecidedPairingUntouched(): void
    {
        $app = $this->getTestApp();
        $event = $this->testEvent($app);
        $approved = $this->createDevicePairing($app, $event, '407215');
        $denied = $this->createDevicePairing($app, $event, '407216');
        $participant = $this->createPaidIstParticipant($app);
        $intruder = $this->createPaidIstParticipant($app);
        $service = $this->getService($app, DevicePairingService::class);
        $repository = $this->getService($app, DevicePairingRepository::class);
        $staleApproved = $repository->get($approved->id);
        $staleDenied = $repository->get($denied->id);

        self::assertTrue($service->approve($repository->get($approved->id), $participant));
        self::assertTrue($service->deny($repository->get($denied->id)));

        self::assertFalse($service->approve($staleApproved, $intruder));
        self::assertFalse($service->deny($staleApproved));
        self::assertFalse($service->approve($staleDenied, $intruder));
        $storedApproved = $repository->get($approved->id);
        self::assertSame('approved', $storedApproved->status->value);
        self::assertSame($participant->id, $storedApproved->participant?->id);
        $storedDenied = $repository->get($denied->id);
        self::assertSame('denied', $storedDenied->status->value);
        self::assertNull($storedDenied->participant);
    }

    public function testResubmitAfterPhoneCollectedPairingFlashesUsedAndRecordsNoAttempt(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $event = $this->testEvent($app);
        $pairing = $this->createDevicePairing($app, $event, '730915');
        $participant = $this->createPaidIstParticipant($app);
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        $this->post($app, self::CONFIRM_PATH, ['code' => '730915', 'decision' => 'approve']);
        $this->flashed($app);
        $this->getService($app, DevicePairingService::class)->poll($pairing->deviceCode, $event);
        $response = $this->post($app, self::CONFIRM_PATH, ['code' => '730915', 'decision' => 'approve']);

        $this->assertRedirectsToForm($response);
        self::assertContains($this->translate($app, 'flash.warning.devicePairingCodeUsed'), $this->flashed($app));
        $stored = $this->getService($app, DevicePairingRepository::class)->get($pairing->id);
        self::assertSame('consumed', $stored->status->value);
        self::assertSame(0, $this->countAttempts($app, $participant));
    }

    public function testUsedCodeInCheckFormFlashesUsed(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $this->createDevicePairing($app, $this->testEvent($app), '730916', status: DevicePairingStatus::Consumed);
        $participant = $this->createPaidIstParticipant($app);
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        $response = $this->post($app, self::PAIRING_PATH, ['code' => '730916']);

        $this->assertRedirectsToForm($response);
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

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('name="decision" value="approve"', (string)$response->getBody());
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

        $this->assertRedirectsToForm($response);
        self::assertContains($this->translate($app, 'flash.warning.devicePairingCodeInvalid'), $this->flashed($app));
        self::assertSame(1, $this->countAttempts($app, $participant));
    }

    public function testLockedOutParticipantGetsTooManyAttemptsForUsedCode(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $this->createDevicePairing($app, $this->testEvent($app), '730918', status: DevicePairingStatus::Denied);
        $participant = $this->createPaidIstParticipant($app);
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        $this->expectLog(Level::Warning, 'Device pairing refused, participant locked out');
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->post($app, self::PAIRING_PATH, ['code' => '000000']);
        }
        $this->flashed($app);

        $response = $this->post($app, self::PAIRING_PATH, ['code' => '730918']);

        $this->assertRedirectsToForm($response);
        self::assertContains(
            $this->translate($app, 'flash.warning.devicePairingTooManyAttempts'),
            $this->flashed($app),
        );
    }

    public function testConfirmWithoutCsrfTokenIsRejected(): void
    {
        $app = $this->getTestApp();
        $this->setEventKey($app, 'pairing-web-event-key');
        $pairing = $this->createDevicePairing($app, $this->testEvent($app), '618374');
        $participant = $this->createPaidIstParticipant($app);
        $app = $this->loginAs($app, $participant->getUserButNotNull());

        $response = $app->handle($this->createRequest(
            self::CONFIRM_PATH,
            'POST',
            ['code' => '618374', 'decision' => 'approve'],
        ));

        $this->assertRedirectsToForm($response);
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

        $lockedOut = $this->post($app, self::CONFIRM_PATH, ['code' => '935126', 'decision' => 'approve']);

        $this->assertRedirectsToForm($lockedOut);
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

        $response = $this->post($app, self::CONFIRM_PATH, ['code' => '935126', 'decision' => 'approve']);

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

    private function assertRedirectsToForm(ResponseInterface $response): void
    {
        self::assertSame(302, $response->getStatusCode());
        self::assertSame(self::PAIRING_PATH, $response->getHeaderLine('Location'));
    }

    private function assertRedirectsToDashboard(ResponseInterface $response): void
    {
        self::assertSame(302, $response->getStatusCode());
        self::assertSame(self::DASHBOARD_PATH, $response->getHeaderLine('Location'));
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
