<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\Payment\PaymentRepository;
use kissj\Payment\PaymentStatus;
use kissj\User\UserRepository;
use kissj\User\UserStatus;
use LogicException;
use Phinx\Console\PhinxApplication;
use RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\AppTestCase;

class TestingEventsSeedTest extends AppTestCase
{
    private const array SEEDED_SLUGS = ['korbo3026', 'navigamus3027', 'obrok3027', 'nsj3025', 'cej3026'];

    public function testSeedsFiveEventsWithAdminsIdempotently(): void
    {
        $app = $this->getTestApp();
        /** @var EventRepository $eventRepository */
        $eventRepository = $this->getService($app, EventRepository::class);
        /** @var UserRepository $userRepository */
        $userRepository = $this->getService($app, UserRepository::class);

        $eventCountBefore = count($eventRepository->findAll());

        $this->runSeed();

        self::assertCount($eventCountBefore + 5, $eventRepository->findAll());
        foreach (self::SEEDED_SLUGS as $slug) {
            $event = $eventRepository->findBySlug($slug);
            self::assertNotNull($event, 'missing seeded event ' . $slug);
            self::assertNotNull($userRepository->findUserFromEmail('a@a.a', $event), 'missing admin on ' . $slug);
        }

        $navigamus = $eventRepository->findBySlug('navigamus3027');
        self::assertNotNull($navigamus);
        self::assertSame('Navigamus 3027', $navigamus->readableName);
        self::assertSame('/logo_navigamus27_color.png', $navigamus->logoUrl);
        // empty skautisAppId would 500 the login page of skautis-enabled event types
        self::assertNotSame('', $navigamus->skautisAppId);
        self::assertTrue($navigamus->allowPatrols);
        self::assertFalse($navigamus->allowTroops);
        self::assertSame(1, $navigamus->minimalPatrolParticipantsCount);

        $korbo = $eventRepository->findBySlug('korbo3026');
        self::assertNotNull($korbo);
        self::assertTrue($korbo->allowIsts);
        self::assertFalse($korbo->allowPatrols);
        self::assertFalse($korbo->allowGuests);

        $this->runSeed();
        self::assertCount($eventCountBefore + 5, $eventRepository->findAll());
    }

    public function testSeedsSimpleRoleParticipantsWithStatusSplitAndPayments(): void
    {
        $app = $this->getTestApp();
        /** @var EventRepository $eventRepository */
        $eventRepository = $this->getService($app, EventRepository::class);
        /** @var ParticipantRepository $participantRepository */
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        /** @var PaymentRepository $paymentRepository */
        $paymentRepository = $this->getService($app, PaymentRepository::class);

        $this->runSeed();

        $korbo = $eventRepository->findBySlug('korbo3026');
        self::assertNotNull($korbo);
        self::assertSame(20, $participantRepository->getParticipantsCount([ParticipantRole::Ist], UserStatus::cases(), $korbo));
        self::assertSame(5, $participantRepository->getParticipantsCount([ParticipantRole::Ist], [UserStatus::Paid], $korbo));
        self::assertSame(5, $participantRepository->getParticipantsCount([ParticipantRole::Ist], [UserStatus::Open], $korbo));
        self::assertSame(0, $participantRepository->getParticipantsCount([ParticipantRole::Guest], UserStatus::cases(), $korbo));

        $navigamus = $eventRepository->findBySlug('navigamus3027');
        self::assertNotNull($navigamus);
        self::assertSame(20, $participantRepository->getParticipantsCount([ParticipantRole::Guest], UserStatus::cases(), $navigamus));
        self::assertSame(20, $participantRepository->getParticipantsCount([ParticipantRole::OrganizingTeam], UserStatus::cases(), $navigamus));

        // guest/ot payments price the discounted rate, not the event's default_price
        $paidGuest = $participantRepository->findOneBy(['email' => 'guest20@navigamus3027.test']);
        self::assertNotNull($paidGuest);
        $guestPayments = $paymentRepository->findByParticipant($paidGuest);
        self::assertCount(1, $guestPayments);
        self::assertSame('500', $guestPayments[0]->price);

        $cej = $eventRepository->findBySlug('cej3026');
        self::assertNotNull($cej);
        // cej cannot price guest/ot - deliberately excluded from its role list
        self::assertSame(0, $participantRepository->getParticipantsCount([ParticipantRole::Guest], UserStatus::cases(), $cej));
        self::assertSame(0, $participantRepository->getParticipantsCount([ParticipantRole::OrganizingTeam], UserStatus::cases(), $cej));

        $cejIst = $participantRepository->findOneBy(['email' => 'ist20@cej3026.test']);
        self::assertNotNull($cejIst);
        self::assertSame('detail.contingent.czechia', $cejIst->contingent);

        $paidIst = $participantRepository->findOneBy(['email' => 'ist20@korbo3026.test']);
        self::assertNotNull($paidIst);
        $payments = $paymentRepository->findByParticipant($paidIst);
        self::assertCount(1, $payments);
        self::assertSame(PaymentStatus::Paid, $payments[0]->status);

        $this->runSeed();
        self::assertSame(20, $participantRepository->getParticipantsCount([ParticipantRole::Ist], UserStatus::cases(), $korbo));

        // re-run must not duplicate payments for an already-seeded participant
        $paidIstAfterRerun = $participantRepository->findOneBy(['email' => 'ist20@korbo3026.test']);
        self::assertNotNull($paidIstAfterRerun);
        $paymentsAfterRerun = $paymentRepository->findByParticipant($paidIstAfterRerun);
        self::assertCount(1, $paymentsAfterRerun);
        self::assertSame(PaymentStatus::Paid, $paymentsAfterRerun[0]->status);
    }

    public function testSeedsPatrolsAndTroops(): void
    {
        $app = $this->getTestApp();
        /** @var EventRepository $eventRepository */
        $eventRepository = $this->getService($app, EventRepository::class);
        /** @var ParticipantRepository $participantRepository */
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        /** @var PaymentRepository $paymentRepository */
        $paymentRepository = $this->getService($app, PaymentRepository::class);

        $this->runSeed();

        $navigamus = $eventRepository->findBySlug('navigamus3027');
        self::assertNotNull($navigamus);
        self::assertSame(20, $participantRepository->getParticipantsCount([ParticipantRole::PatrolLeader], UserStatus::cases(), $navigamus));
        self::assertSame(20, $participantRepository->getParticipantsCount([ParticipantRole::PatrolParticipant], UserStatus::cases(), $navigamus));
        // paid PLs carry 2 PPs each
        self::assertSame(10, $participantRepository->getParticipantsCount([ParticipantRole::PatrolParticipant], [UserStatus::Paid], $navigamus));

        // PPs have no user of their own - the app never creates one for them
        $pp1 = $participantRepository->findOneBy(['email' => 'pp1@navigamus3027.test']);
        self::assertNotNull($pp1);
        self::assertNull($pp1->user);

        $navigamusPl = $participantRepository->findOneBy(['email' => 'pl20@navigamus3027.test']);
        self::assertNotNull($navigamusPl);
        self::assertSame('detail.contingent.without', $navigamusPl->contingent);

        $obrok = $eventRepository->findBySlug('obrok3027');
        self::assertNotNull($obrok);
        self::assertSame(20, $participantRepository->getParticipantsCount([ParticipantRole::TroopLeader], UserStatus::cases(), $obrok));
        self::assertSame(20, $participantRepository->getParticipantsCount([ParticipantRole::TroopParticipant], UserStatus::cases(), $obrok));
        self::assertSame(0, $participantRepository->getParticipantsCount([ParticipantRole::PatrolLeader], UserStatus::cases(), $obrok));

        // TPs reach paid via their leader's payment cascade - the app never creates a TP payment directly
        $obrokTp = $participantRepository->findOneBy(['email' => 'tp20@obrok3027.test']);
        self::assertNotNull($obrokTp);
        self::assertCount(0, $paymentRepository->findByParticipant($obrokTp));

        $this->runSeed();
        self::assertSame(20, $participantRepository->getParticipantsCount([ParticipantRole::PatrolParticipant], UserStatus::cases(), $navigamus));
    }

    private function runSeed(): void
    {
        // tripwire mirrors AppTestCase::provideCleanDatabase() - refuse to run phinx against
        // anything but the per-test sqlite database
        if ($_ENV['DB_TYPE'] !== 'sqlite') {
            throw new LogicException('Seed test must run on sqlite — refusing to touch a real database.');
        }

        $phinx = new PhinxApplication();
        $phinx->setAutoExit(false);
        $output = new BufferedOutput();
        $exitCode = $phinx->run(new ArrayInput([
            'command' => 'seed:run',
            '--configuration' => __DIR__ . '/../phinxConfiguration.php',
            // --seed is declared VALUE_IS_ARRAY by phinx; ArrayInput needs an actual array or it silently no-ops
            '--seed' => ['TestingEventsSeed'],
        ]), $output);
        if ($exitCode !== 0) {
            throw new RuntimeException('seed run failed: ' . $output->fetch());
        }
    }
}
