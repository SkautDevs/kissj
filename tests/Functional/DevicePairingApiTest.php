<?php

declare(strict_types=1);

namespace Tests\Functional;

use DateTimeImmutable;
use kissj\Application\DateTimeUtils;
use kissj\Programme\Device\DevicePairing;
use kissj\Programme\Device\DevicePairingRepository;
use kissj\Programme\Device\DevicePairingService;
use kissj\Programme\Device\DevicePairingStatus;
use kissj\Programme\Device\DeviceSessionRepository;
use Monolog\Level;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App;
use Tests\AppTestCase;

class DevicePairingApiTest extends AppTestCase
{
    private const string EVENT_SLUG = 'test-event-slug';
    private const string API_KEY = 'device-pairing-test-key';
    private const string OTHER_EVENT_SLUG = 'device-pairing-other-event';

    public function testStartAnswersCodesUrlExpiryAndInterval(): void
    {
        $app = $this->getTestApp();
        $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);

        $response = $app->handle($this->createPairingStartRequest(self::API_KEY));

        self::assertSame(200, $response->getStatusCode());
        $body = $this->decodeJson($response);
        self::assertIsString($body['deviceCode']);
        self::assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $body['deviceCode']);
        self::assertIsString($body['userCode']);
        self::assertMatchesRegularExpression('/^\d{6}$/', $body['userCode']);
        self::assertIsString($body['verificationUrl']);
        self::assertStringEndsWith('/v2/event/test-event-slug/participant/pairing', $body['verificationUrl']);
        self::assertSame(5, $body['interval']);
        self::assertIsString($body['expiresAt']);
        $expiresAt = DateTimeImmutable::createFromFormat(DATE_ATOM, $body['expiresAt']);
        self::assertNotFalse($expiresAt);
        self::assertEqualsWithDelta(
            DateTimeUtils::getDateTime('+1 hour')->getTimestamp(),
            $expiresAt->getTimestamp(),
            60,
        );

        $pairing = $this->getService($app, DevicePairingRepository::class)->findByDeviceCode(
            $body['deviceCode'],
            $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY),
        );
        self::assertNotNull($pairing);
        self::assertSame(DevicePairingStatus::Pending, $pairing->status);
        self::assertSame($body['userCode'], $pairing->userCode);
    }

    public function testStartRefusesAtThousandPendingButExpiredOnesDoNotCount(): void
    {
        $app = $this->getTestApp();
        $event = $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);
        $now = DateTimeUtils::getDateTime();
        for ($index = 0; $index < 999; ++$index) {
            $this->createDevicePairing($app, $event, expiresAt: $now->modify('+1 hour'));
        }
        $this->createDevicePairing($app, $event, expiresAt: $now->modify('-1 minute'));

        $accepted = $app->handle($this->createPairingStartRequest(self::API_KEY));
        $this->expectLog(Level::Warning, 'Device pairing refused, pending cap reached');
        $refused = $app->handle($this->createPairingStartRequest(self::API_KEY));

        self::assertSame(200, $accepted->getStatusCode());
        self::assertSame(429, $refused->getStatusCode());
        self::assertSame('text/plain', $refused->getHeaderLine('Content-Type'));
        self::assertSame('Too many pending pairings', (string)$refused->getBody());
    }

    public function testStartWithoutValidDeviceIdIsRejected(): void
    {
        $app = $this->getTestApp();
        $event = $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);
        $withoutBody = $this->createApiRequest('/v3/programme/pairing/start', self::API_KEY, 'POST');
        $requests = [$withoutBody];
        $invalidDeviceIds = [
            5,
            str_repeat('A', 64),
            str_repeat('a', 63),
            str_repeat('g', 64),
            str_repeat('a', 64) . "\n",
        ];
        foreach ($invalidDeviceIds as $deviceId) {
            $request = $this->createApiRequest('/v3/programme/pairing/start', self::API_KEY, 'POST');
            $request->getBody()->write(json_encode(['deviceId' => $deviceId], JSON_THROW_ON_ERROR));
            $requests[] = $request;
        }

        foreach ($requests as $request) {
            $response = $app->handle($request);

            self::assertSame(400, $response->getStatusCode());
            self::assertSame('Invalid deviceId', (string)$response->getBody());
            self::assertSame('text/plain', $response->getHeaderLine('Content-Type'));
        }
        self::assertSame(0, $this->getService($app, DevicePairingRepository::class)->countBy(['event' => $event]));
    }

    public function testStartFromSameDeviceReplacesOnlyItsPendingPairing(): void
    {
        $app = $this->getTestApp();
        $event = $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);
        $deviceId = str_repeat('ab', 32);
        $otherDeviceId = str_repeat('cd', 32);
        $approved = $this->createDevicePairing(
            $app,
            $event,
            status: DevicePairingStatus::Approved,
            deviceId: $deviceId,
        );
        $denied = $this->createDevicePairing(
            $app,
            $event,
            status: DevicePairingStatus::Denied,
            deviceId: $deviceId,
        );
        $consumed = $this->createDevicePairing(
            $app,
            $event,
            status: DevicePairingStatus::Consumed,
            deviceId: $deviceId,
        );
        $staleExpired = $this->createDevicePairing(
            $app,
            $event,
            expiresAt: DateTimeUtils::getDateTime('-1 minute'),
            deviceId: $deviceId,
        );
        $otherDevice = $this->createDevicePairing($app, $event, deviceId: $otherDeviceId);

        $first = $this->decodeJson($app->handle($this->createPairingStartRequest(self::API_KEY, $deviceId)));
        $second = $this->decodeJson($app->handle($this->createPairingStartRequest(self::API_KEY, $deviceId)));

        $repository = $this->getService($app, DevicePairingRepository::class);
        $pendingOfDevice = $repository->findBy([
            'event' => $event,
            'device_id' => $deviceId,
            'status' => 'pending',
        ]);
        self::assertCount(1, $pendingOfDevice);
        self::assertSame($second['deviceCode'], $pendingOfDevice[0]->deviceCode);
        self::assertSame('replaced', $repository->get($staleExpired->id)->status->value);
        self::assertIsString($first['deviceCode']);
        $firstPairing = $repository->findByDeviceCode($first['deviceCode'], $event);
        self::assertSame('replaced', $firstPairing?->status->value);
        foreach ([$approved, $denied, $consumed, $otherDevice] as $kept) {
            self::assertSame($kept->status, $repository->get($kept->id)->status);
        }
        self::assertSame('{"status":"expired"}', (string)$this->poll($app, $first['deviceCode'])->getBody());
        self::assertSame('{"status":"pending"}', (string)$this->poll($app, $second['deviceCode'])->getBody());
    }

    public function testPollPendingThenSlowDown(): void
    {
        $app = $this->getTestApp();
        $pairing = $this->createDevicePairing($app, $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY));

        $first = $this->poll($app, $pairing->deviceCode);
        $second = $this->poll($app, $pairing->deviceCode);
        $pairing->lastPolledAt = DateTimeUtils::getDateTime('-6 seconds');
        $this->getService($app, DevicePairingRepository::class)->persist($pairing);
        $third = $this->poll($app, $pairing->deviceCode);

        self::assertSame('{"status":"pending"}', (string)$first->getBody());
        self::assertSame('{"status":"slowDown"}', (string)$second->getBody());
        self::assertSame('{"status":"pending"}', (string)$third->getBody());
    }

    public function testPollUnknownExpiredOrForeignCodeIsExpired(): void
    {
        $app = $this->getTestApp();
        $event = $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);
        $expired = $this->createDevicePairing($app, $event, expiresAt: DateTimeUtils::getDateTime('-1 minute'));
        $otherEvent = $this->getTypedTestEvent($app, 'default', self::OTHER_EVENT_SLUG);
        $foreign = $this->createDevicePairing($app, $otherEvent);

        foreach ([bin2hex(random_bytes(16)), $expired->deviceCode, $foreign->deviceCode] as $deviceCode) {
            self::assertSame('{"status":"expired"}', (string)$this->poll($app, $deviceCode)->getBody());
        }
    }

    public function testPollWithoutDeviceCodeIsExpired(): void
    {
        $app = $this->getTestApp();
        $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);

        $empty = $app->handle($this->createApiRequest('/v3/programme/pairing/poll', self::API_KEY, 'POST'));
        $nonString = $this->poll($app, 5);

        self::assertSame('{"status":"expired"}', (string)$empty->getBody());
        self::assertSame('{"status":"expired"}', (string)$nonString->getBody());
    }

    public function testScalarJsonBodiesAreTreatedAsEmpty(): void
    {
        $app = $this->getTestApp();
        $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);
        $start = $this->createApiRequest('/v3/programme/pairing/start', self::API_KEY, 'POST');
        $start->getBody()->write('5');
        $poll = $this->createApiRequest('/v3/programme/pairing/poll', self::API_KEY, 'POST');
        $poll->getBody()->write('"x"');

        $started = $app->handle($start);
        $polled = $app->handle($poll);

        self::assertSame(400, $started->getStatusCode());
        self::assertSame('Invalid deviceId', (string)$started->getBody());
        self::assertSame('{"status":"expired"}', (string)$polled->getBody());
    }

    public function testPollDenied(): void
    {
        $app = $this->getTestApp();
        $pairing = $this->createDevicePairing(
            $app,
            $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY),
            status: DevicePairingStatus::Denied,
        );

        $response = $this->poll($app, $pairing->deviceCode);

        self::assertSame('{"status":"denied"}', (string)$response->getBody());
    }

    public function testPollApprovedOnceThenExpired(): void
    {
        $app = $this->getTestApp();
        $event = $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);
        $pairing = $this->createDevicePairing($app, $event);
        $participant = $this->createPaidIstOnEvent($app, $event, 'Pairing');
        $this->getService($app, DevicePairingService::class)->approve($pairing, $participant);

        $approved = $this->poll($app, $pairing->deviceCode);

        self::assertSame(200, $approved->getStatusCode());
        $body = $this->decodeJson($approved);
        self::assertSame('approved', $body['status']);
        self::assertIsString($body['sessionToken']);
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $body['sessionToken']);
        self::assertIsString($body['expiresAt']);
        $expiresAt = DateTimeImmutable::createFromFormat(DATE_ATOM, $body['expiresAt']);
        self::assertNotFalse($expiresAt);
        self::assertEqualsWithDelta(
            DateTimeUtils::getDateTime('+6 months')->getTimestamp(),
            $expiresAt->getTimestamp(),
            60,
        );

        $session = $this->getService($app, DeviceSessionRepository::class)->findOneBy([
            'token_hash' => hash('sha256', $body['sessionToken']),
        ]);
        self::assertNotNull($session);
        self::assertSame($participant->id, $session->participant->id);
        $stored = $this->getService($app, DevicePairingRepository::class)->get($pairing->id);
        self::assertSame(DevicePairingStatus::Consumed, $stored->status);

    }

    public function testPollAfterLostApprovedAnswerReissuesTokenOfSameSession(): void
    {
        $app = $this->getTestApp();
        $event = $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);
        $pairing = $this->createDevicePairing($app, $event);
        $participant = $this->createPaidIstOnEvent($app, $event, 'Reissue');
        $this->getService($app, DevicePairingService::class)->approve($pairing, $participant);
        $lost = $this->decodeJson($this->poll($app, $pairing->deviceCode));
        $this->movePollBack($app, $pairing);

        $reissued = $this->decodeJson($this->poll($app, $pairing->deviceCode));

        self::assertSame('approved', $reissued['status']);
        self::assertIsString($lost['sessionToken']);
        self::assertIsString($reissued['sessionToken']);
        self::assertNotSame($lost['sessionToken'], $reissued['sessionToken']);
        $sessions = $this->getService($app, DeviceSessionRepository::class);
        self::assertNull($sessions->findByTokenHash(hash('sha256', $lost['sessionToken'])));
        self::assertNotNull($sessions->findByTokenHash(hash('sha256', $reissued['sessionToken'])));
        self::assertCount(1, $sessions->findBy(['participant' => $participant]));
    }

    public function testPollOfConsumedPairingAfterOneMinuteIsExpired(): void
    {
        $app = $this->getTestApp();
        $event = $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);
        $pairing = $this->createDevicePairing($app, $event);
        $participant = $this->createPaidIstOnEvent($app, $event, 'Late');
        $this->getService($app, DevicePairingService::class)->approve($pairing, $participant);
        $issued = $this->decodeJson($this->poll($app, $pairing->deviceCode));
        $repository = $this->getService($app, DevicePairingRepository::class);
        $consumed = $repository->get($pairing->id);
        $consumed->expiresAt = DateTimeUtils::getDateTime('-1 minute');
        $consumed->lastPolledAt = DateTimeUtils::getDateTime('-6 seconds');
        $repository->persist($consumed);

        $late = $this->poll($app, $pairing->deviceCode);

        self::assertSame('{"status":"expired"}', (string)$late->getBody());
        self::assertIsString($issued['sessionToken']);
        $sessions = $this->getService($app, DeviceSessionRepository::class);
        self::assertNotNull($sessions->findByTokenHash(hash('sha256', $issued['sessionToken'])));
    }

    public function testPollApprovedPairingShortlyAfterItsExpiryStillIssuesSession(): void
    {
        $app = $this->getTestApp();
        $event = $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);
        $pairing = $this->createDevicePairing($app, $event, expiresAt: DateTimeUtils::getDateTime('+1 second'));
        $participant = $this->createPaidIstOnEvent($app, $event, 'Pairing');
        $this->getService($app, DevicePairingService::class)->approve($pairing, $participant);
        $pairing->expiresAt = DateTimeUtils::getDateTime('-30 seconds');
        $this->getService($app, DevicePairingRepository::class)->persist($pairing);

        $body = $this->decodeJson($this->poll($app, $pairing->deviceCode));

        self::assertSame('approved', $body['status']);
        self::assertIsString($body['sessionToken']);
        $stored = $this->getService($app, DevicePairingRepository::class)->get($pairing->id);
        self::assertSame(DevicePairingStatus::Consumed, $stored->status);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function movePollBack(App $app, DevicePairing $pairing): void
    {
        $repository = $this->getService($app, DevicePairingRepository::class);
        $stored = $repository->get($pairing->id);
        $stored->lastPolledAt = DateTimeUtils::getDateTime('-6 seconds');
        $repository->persist($stored);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function poll(App $app, mixed $deviceCode): Response
    {
        $request = $this->createApiRequest('/v3/programme/pairing/poll', self::API_KEY, 'POST');
        $request->getBody()->write(json_encode(['deviceCode' => $deviceCode], JSON_THROW_ON_ERROR));

        return $app->handle($request);
    }

}
