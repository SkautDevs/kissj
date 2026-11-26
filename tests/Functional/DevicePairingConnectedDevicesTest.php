<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Device\DevicePairingRepository;
use kissj\Device\DevicePairingService;
use kissj\Device\DevicePairingStatus;
use kissj\Device\DeviceSession;
use kissj\Device\DeviceSessionRepository;
use kissj\Event\EventRepository;
use kissj\Participant\Participant;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class DevicePairingConnectedDevicesTest extends AppTestCase
{
    public function testConnectedDevicesCountActiveSessionsAndUncollectedApprovals(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $participant = $this->createPaidIstOnEvent($app, $event, 'owner');
        $other = $this->createPaidIstOnEvent($app, $event, 'other');

        $this->createSession($app, $participant, '+1 month');
        $this->createSession($app, $participant, '+6 months');
        $this->createSession($app, $participant, '-1 minute');
        $this->createPairing($app, $participant, DevicePairingStatus::Approved, '+1 minute');
        $this->createPairing($app, $participant, DevicePairingStatus::Approved, '-2 minutes');
        $this->createPairing($app, $participant, DevicePairingStatus::Consumed, '+1 minute');
        $this->createPairing($app, $participant, DevicePairingStatus::Pending, '+1 minute');
        $this->createSession($app, $other, '+1 month');
        $this->createPairing($app, $other, DevicePairingStatus::Approved, '+1 minute');

        self::assertSame(3, $this->getService($app, DevicePairingService::class)->countConnectedDevices($participant));
    }

    public function testApprovedPairingIsCountedInsideGraceMinute(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $participant = $this->createPaidIstOnEvent($app, $event, 'graced');
        $this->createPairing($app, $participant, DevicePairingStatus::Approved, '-30 seconds');
        self::assertSame(1, $this->getService($app, DevicePairingService::class)->countConnectedDevices($participant));

        $this->createPairing($app, $participant, DevicePairingStatus::Approved, '-2 minutes');
        self::assertSame(1, $this->getService($app, DevicePairingService::class)->countConnectedDevices($participant));
    }

    public function testConnectedDevicesIsZeroWithoutSessions(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $participant = $this->createPaidIstOnEvent($app, $event, 'lonely');

        self::assertSame(0, $this->getService($app, DevicePairingService::class)->countConnectedDevices($participant));
    }

    public function testEventAppUrlJoinsBaseAndSlug(): void
    {
        $this->setEnv('EVENT_APP_URL', 'https://app.example.org/');
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        self::assertSame(
            'https://app.example.org/test-slug/',
            $this->getService($app, DevicePairingService::class)->eventAppUrl($event),
        );

        $this->setEnv('EVENT_APP_URL', 'https://app.example.org');
        $app = $this->getTestApp();
        self::assertSame(
            'https://app.example.org/test-slug/',
            $this->getService($app, DevicePairingService::class)->eventAppUrl($event),
        );

        $this->setEnv('EVENT_APP_URL', '');
        $app = $this->getTestApp();
        self::assertNull($this->getService($app, DevicePairingService::class)->eventAppUrl($event));
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createSession(App $app, Participant $participant, string $expiresIn): void
    {
        $session = new DeviceSession();
        $session->participant = $participant;
        $session->tokenHash = DeviceSession::hashToken(bin2hex(random_bytes(32)));
        $session->expiresAt = DateTimeUtils::getDateTime($expiresIn);
        $this->getService($app, DeviceSessionRepository::class)->persist($session);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createPairing(
        App $app,
        Participant $participant,
        DevicePairingStatus $status,
        string $expiresIn,
    ): void {
        $pairing = $this->createDevicePairing(
            $app,
            $participant->getUserButNotNull()->event,
            status: $status,
            expiresAt: DateTimeUtils::getDateTime($expiresIn),
        );
        $pairing->participant = $participant;
        $this->getService($app, DevicePairingRepository::class)->persist($pairing);
    }
}
