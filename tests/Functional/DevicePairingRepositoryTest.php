<?php

declare(strict_types=1);

namespace Tests\Functional;

use DateTimeInterface;
use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\Participant;
use kissj\Programme\Device\DevicePairingAttempt;
use kissj\Programme\Device\DevicePairingAttemptRepository;
use kissj\Programme\Device\DevicePairingRepository;
use kissj\Programme\Device\DevicePairingStatus;
use kissj\Programme\Device\DeviceSession;
use kissj\Programme\Device\DeviceSessionRepository;
use LogicException;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class DevicePairingRepositoryTest extends AppTestCase
{
    public function testFindPendingByUserCodeIgnoresExpiredDeniedAndForeignEvent(): void
    {
        $app = $this->getTestApp();
        $repository = $this->getService($app, DevicePairingRepository::class);
        $event = $this->ownEvent($app);
        $foreignEvent = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $now = DateTimeUtils::getDateTime();

        $this->createDevicePairing($app, $event, '123456', expiresAt: $now->modify('-1 minute'));
        $this->createDevicePairing(
            $app,
            $event,
            '123456',
            status: DevicePairingStatus::Denied,
            expiresAt: $now->modify('+1 hour'),
        );
        $this->createDevicePairing($app, $foreignEvent, '123456', expiresAt: $now->modify('+1 hour'));
        $pending = $this->createDevicePairing($app, $event, '123456', expiresAt: $now->modify('+1 hour'));

        $found = $repository->findPendingByUserCode('123456', $event, $now);

        self::assertNotNull($found);
        self::assertSame($pending->id, $found->id);
        self::assertNull($repository->findPendingByUserCode('654321', $event, $now));
    }

    public function testExistsCreatedAfterMatchesAnyStatusWithinWindowOfOwnEvent(): void
    {
        $app = $this->getTestApp();
        $repository = $this->getService($app, DevicePairingRepository::class);
        $event = $this->ownEvent($app);
        $foreignEvent = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $now = DateTimeUtils::getDateTime();

        $recent = $this->createDevicePairing(
            $app,
            $event,
            '111111',
            status: DevicePairingStatus::Consumed,
        );
        $recent->createdAt = $now->modify('-59 minutes');
        $repository->persist($recent);
        $old = $this->createDevicePairing(
            $app,
            $event,
            '222222',
            status: DevicePairingStatus::Denied,
        );
        $old->createdAt = $now->modify('-61 minutes');
        $repository->persist($old);
        $this->createDevicePairing(
            $app,
            $foreignEvent,
            '333333',
            status: DevicePairingStatus::Consumed,
        );
        $boundary = $this->createDevicePairing($app, $event, '444444', status: DevicePairingStatus::Denied);
        $boundary->createdAt = $now->modify('-1 hour');
        $repository->persist($boundary);
        $after = $now->modify('-1 hour');

        self::assertTrue($repository->existsCreatedAfter('111111', $event, $after));
        self::assertFalse($repository->existsCreatedAfter('222222', $event, $after));
        self::assertFalse($repository->existsCreatedAfter('333333', $event, $after));
        self::assertFalse($repository->existsCreatedAfter('444444', $event, $after));
    }

    public function testCountPendingUnexpiredIgnoresExpiredAndConsumed(): void
    {
        $app = $this->getTestApp();
        $repository = $this->getService($app, DevicePairingRepository::class);
        $event = $this->ownEvent($app);
        $now = DateTimeUtils::getDateTime();

        $this->createDevicePairing($app, $event, '111111', expiresAt: $now->modify('+1 hour'));
        $this->createDevicePairing($app, $event, '222222', expiresAt: $now->modify('+2 hours'));
        $this->createDevicePairing($app, $event, '333333', expiresAt: $now->modify('-1 hour'));
        $this->createDevicePairing(
            $app,
            $event,
            '444444',
            status: DevicePairingStatus::Consumed,
            expiresAt: $now->modify('+1 hour'),
        );

        self::assertSame(2, $repository->countPendingUnexpired($event, $now));
    }

    public function testFindByDeviceCodeIsScopedToEvent(): void
    {
        $app = $this->getTestApp();
        $repository = $this->getService($app, DevicePairingRepository::class);
        $event = $this->ownEvent($app);
        $foreignEvent = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $pairing = $this->createDevicePairing($app, $event, '555555');

        $found = $repository->findByDeviceCode($pairing->deviceCode, $event);

        self::assertNotNull($found);
        self::assertSame($pairing->id, $found->id);
        self::assertNull($repository->findByDeviceCode($pairing->deviceCode, $foreignEvent));
        self::assertNull($repository->findByDeviceCode('0000', $event));
    }

    public function testFindByTokenHash(): void
    {
        $app = $this->getTestApp();
        $repository = $this->getService($app, DeviceSessionRepository::class);
        $participant = $this->createParticipantWithRole($this->container($app), 'ist');

        $session = new DeviceSession();
        $session->participant = $participant;
        $session->tokenHash = DeviceSession::hashToken('known-token');
        $session->expiresAt = DateTimeUtils::getDateTime('+6 months');
        $repository->persist($session);

        $found = $repository->findByTokenHash(DeviceSession::hashToken('known-token'));

        self::assertNotNull($found);
        self::assertSame($session->id, $found->id);
        self::assertSame($participant->id, $found->participant->id);
        self::assertNull($repository->findByTokenHash(DeviceSession::hashToken('other-token')));
    }

    public function testAttemptCountAndPrune(): void
    {
        $app = $this->getTestApp();
        $repository = $this->getService($app, DevicePairingAttemptRepository::class);
        $participant = $this->createParticipantWithRole($this->container($app), 'ist');
        $now = DateTimeUtils::getDateTime();

        $this->createAttempt($repository, $participant, $now->modify('-2 hours'));
        $this->createAttempt($repository, $participant, $now->modify('-10 minutes'));
        $this->createAttempt($repository, $participant, $now);

        self::assertSame(2, $repository->countSince($participant, $now->modify('-1 hour')));

        $repository->deleteOlderThan($participant, $now->modify('-1 hour'));

        self::assertSame(2, $repository->countBy(['participant' => $participant]));
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function ownEvent(App $app): Event
    {
        return $this->getService($app, EventRepository::class)->get(1);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function container(App $app): ContainerInterface
    {
        return $app->getContainer() ?? throw new LogicException('container missing');
    }

    private function createAttempt(
        DevicePairingAttemptRepository $repository,
        Participant $participant,
        DateTimeInterface $createdAt,
    ): void {
        $attempt = new DevicePairingAttempt();
        $attempt->participant = $participant;
        $repository->persist($attempt);
        $attempt->createdAt = $createdAt;
        $repository->persist($attempt);
    }
}
