<?php

declare(strict_types=1);

namespace Tests\Functional;

use DateTimeInterface;
use kissj\Application\DateTimeUtils;
use kissj\Device\DevicePairingAttempt;
use kissj\Device\DevicePairingAttemptRepository;
use kissj\Device\DevicePairingRepository;
use kissj\Device\DevicePairingStatus;
use kissj\Device\DeviceSession;
use kissj\Device\DeviceSessionRepository;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\Participant;
use LogicException;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class DevicePairingRepositoryTest extends AppTestCase
{
    public function testFindPendingByUserCodeIgnoresExpiredReplacedAndForeignEvent(): void
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
            status: DevicePairingStatus::Replaced,
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
            status: DevicePairingStatus::Replaced,
        );
        $old->createdAt = $now->modify('-61 minutes');
        $repository->persist($old);
        $this->createDevicePairing(
            $app,
            $foreignEvent,
            '333333',
            status: DevicePairingStatus::Consumed,
        );
        $boundary = $this->createDevicePairing($app, $event, '444444', status: DevicePairingStatus::Replaced);
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

        $session = $this->createDeviceSession($app, $participant, 'known-token');

        $found = $repository->findByTokenHash(DeviceSession::hashToken('known-token'));

        self::assertNotNull($found);
        self::assertSame($session->id, $found->id);
        self::assertSame($participant->id, $found->participant->id);
        self::assertNull($repository->findByTokenHash(DeviceSession::hashToken('other-token')));
    }

    public function testDeleteExpiredBeforeRemovesOnlyOldPairingsOfOwnEvent(): void
    {
        $app = $this->getTestApp();
        $repository = $this->getService($app, DevicePairingRepository::class);
        $event = $this->ownEvent($app);
        $foreignEvent = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $now = DateTimeUtils::getDateTime();

        $old = $this->createDevicePairing(
            $app,
            $event,
            status: DevicePairingStatus::Consumed,
            expiresAt: $now->modify('-8 days'),
        );
        $recent = $this->createDevicePairing($app, $event, expiresAt: $now->modify('-6 days'));
        $foreign = $this->createDevicePairing($app, $foreignEvent, expiresAt: $now->modify('-8 days'));

        $repository->deleteExpiredBefore($event, $now->modify('-7 days'));

        self::assertNull($repository->findOneBy(['id' => $old->id]));
        self::assertNotNull($repository->findOneBy(['id' => $recent->id]));
        self::assertNotNull($repository->findOneBy(['id' => $foreign->id]));
    }

    public function testDeleteExpiredSessionsKeepsLiveOnesAndNullsPairingReference(): void
    {
        $app = $this->getTestApp();
        $sessions = $this->getService($app, DeviceSessionRepository::class);
        $pairings = $this->getService($app, DevicePairingRepository::class);
        $event = $this->ownEvent($app);
        $participant = $this->createParticipantWithRole($this->container($app), 'ist', $event);
        $now = DateTimeUtils::getDateTime();

        $expired = $this->createDeviceSession($app, $participant, 'expired-token', $now->modify('-8 days'));
        $live = $this->createDeviceSession($app, $participant, 'live-token', $now->modify('+1 month'));
        $pairing = $this->createDevicePairing($app, $event, status: DevicePairingStatus::Consumed);
        $pairing->deviceSession = $expired;
        $pairings->persist($pairing);

        $sessions->deleteExpiredBefore($event, $now->modify('-7 days'));

        self::assertNull($sessions->findOneBy(['id' => $expired->id]));
        self::assertNotNull($sessions->findOneBy(['id' => $live->id]));
        $survivor = $pairings->findOneBy(['id' => $pairing->id]);
        self::assertNotNull($survivor);
        self::assertNull($survivor->deviceSession);
    }

    public function testDeleteExpiredSessionsIsScopedToEvent(): void
    {
        $app = $this->getTestApp();
        $sessions = $this->getService($app, DeviceSessionRepository::class);
        $event = $this->ownEvent($app);
        $foreignEvent = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $now = DateTimeUtils::getDateTime();
        $own = $this->createParticipantWithRole($this->container($app), 'ist', $event);
        $foreign = $this->createParticipantWithRole($this->container($app), 'ist', $foreignEvent);
        $ownMember = $this->createPatrolMemberOnEvent($app, $event, 'own member');
        $foreignMember = $this->createPatrolMemberOnEvent($app, $foreignEvent, 'foreign member');

        $ownSession = $this->createDeviceSession($app, $own, expiresAt: $now->modify('-8 days'));
        $foreignSession = $this->createDeviceSession($app, $foreign, expiresAt: $now->modify('-8 days'));
        $ownMemberSession = $this->createDeviceSession($app, $ownMember, expiresAt: $now->modify('-8 days'));
        $foreignMemberSession = $this->createDeviceSession($app, $foreignMember, expiresAt: $now->modify('-8 days'));

        $sessions->deleteExpiredBefore($event, $now->modify('-7 days'));

        self::assertNull($sessions->findOneBy(['id' => $ownSession->id]));
        self::assertNotNull($sessions->findOneBy(['id' => $foreignSession->id]));
        self::assertNull($sessions->findOneBy(['id' => $ownMemberSession->id]));
        self::assertNotNull($sessions->findOneBy(['id' => $foreignMemberSession->id]));
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
