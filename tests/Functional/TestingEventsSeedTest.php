<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\EventRepository;
use kissj\Event\EventType\EventTypeDefault;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\Payment\PaymentRepository;
use kissj\Payment\PaymentStatus;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeRepository;
use kissj\Programme\ProgrammeSection;
use kissj\Programme\ProgrammeSectionRepository;
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
    private const array SEEDED_SLUGS = [
        'korbo3026',
        'navigamus3027',
        'obrok3027',
        'nsj3025',
        'cej3026',
        'miquik3026',
        'default3026',
    ];

    public function testSeedsSevenEventsWithAdminsIdempotently(): void
    {
        $app = $this->getTestApp();
        /** @var EventRepository $eventRepository */
        $eventRepository = $this->getService($app, EventRepository::class);
        /** @var UserRepository $userRepository */
        $userRepository = $this->getService($app, UserRepository::class);
        /** @var ParticipantRepository $participantRepository */
        $participantRepository = $this->getService($app, ParticipantRepository::class);

        $eventCountBefore = count($eventRepository->findAll());

        $this->runSeed();

        self::assertCount($eventCountBefore + 7, $eventRepository->findAll());
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
        self::assertSame('2026-11-17', $korbo->endDay->format('Y-m-d'));

        $miquik = $eventRepository->findBySlug('miquik3026');
        self::assertNotNull($miquik);
        self::assertTrue($miquik->allowPatrols);
        self::assertTrue($miquik->allowIsts);
        self::assertFalse($miquik->allowTroops);
        /** @var ParticipantRepository $participantRepository */
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        self::assertSame(
            20,
            $participantRepository->getParticipantsCount([ParticipantRole::Ist], UserStatus::cases(), $miquik),
        );

        $default = $eventRepository->findBySlug('default3026');
        self::assertNotNull($default);
        self::assertInstanceOf(EventTypeDefault::class, $default->getEventType());
        self::assertSame('Testovací akce 3026', $default->readableName);
        self::assertSame('/logo_skaut_white_bg_800.png', $default->logoUrl);
        self::assertFileExists(__DIR__ . '/../../public' . $default->logoUrl);
        self::assertNotSame('', $default->skautisAppId);
        self::assertTrue($default->allowPatrols);
        self::assertTrue($default->allowIsts);
        self::assertTrue($default->allowGuests);
        self::assertFalse($default->allowTroops);
        self::assertSame(
            20,
            $participantRepository->getParticipantsCount([ParticipantRole::Guest], UserStatus::cases(), $default),
        );

        $this->runSeed();
        self::assertCount($eventCountBefore + 7, $eventRepository->findAll());
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
        self::assertSame(
            20,
            $participantRepository->getParticipantsCount([ParticipantRole::Ist], UserStatus::cases(), $korbo),
        );
        self::assertSame(
            5,
            $participantRepository->getParticipantsCount([ParticipantRole::Ist], [UserStatus::Paid], $korbo),
        );
        self::assertSame(
            5,
            $participantRepository->getParticipantsCount([ParticipantRole::Ist], [UserStatus::Open], $korbo),
        );
        self::assertSame(
            0,
            $participantRepository->getParticipantsCount([ParticipantRole::Guest], UserStatus::cases(), $korbo),
        );

        $navigamus = $eventRepository->findBySlug('navigamus3027');
        self::assertNotNull($navigamus);
        self::assertSame(
            20,
            $participantRepository->getParticipantsCount([ParticipantRole::Guest], UserStatus::cases(), $navigamus),
        );
        self::assertSame(
            20,
            $participantRepository->getParticipantsCount(
                [ParticipantRole::OrganizingTeam],
                UserStatus::cases(),
                $navigamus,
            ),
        );

        // guest/ot payments price the discounted rate, not the event's default_price
        $paidGuest = $participantRepository->findOneBy(['email' => 'guest20@navigamus3027.test']);
        self::assertNotNull($paidGuest);
        $guestPayments = $paymentRepository->findByParticipant($paidGuest);
        self::assertCount(1, $guestPayments);
        self::assertSame('500', $guestPayments[0]->price);

        $cej = $eventRepository->findBySlug('cej3026');
        self::assertNotNull($cej);
        // cej cannot price guest/ot - deliberately excluded from its role list
        self::assertSame(
            0,
            $participantRepository->getParticipantsCount([ParticipantRole::Guest], UserStatus::cases(), $cej),
        );
        self::assertSame(
            0,
            $participantRepository->getParticipantsCount([ParticipantRole::OrganizingTeam], UserStatus::cases(), $cej),
        );

        $cejIst = $participantRepository->findOneBy(['email' => 'ist20@cej3026.test']);
        self::assertNotNull($cejIst);
        self::assertSame('detail.contingent.czechia', $cejIst->contingent);

        $paidIst = $participantRepository->findOneBy(['email' => 'ist20@korbo3026.test']);
        self::assertNotNull($paidIst);
        $payments = $paymentRepository->findByParticipant($paidIst);
        self::assertCount(1, $payments);
        self::assertSame(PaymentStatus::Paid, $payments[0]->status);

        $this->runSeed();
        self::assertSame(
            20,
            $participantRepository->getParticipantsCount([ParticipantRole::Ist], UserStatus::cases(), $korbo),
        );

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
        self::assertSame(
            20,
            $participantRepository->getParticipantsCount(
                [ParticipantRole::PatrolLeader],
                UserStatus::cases(),
                $navigamus,
            ),
        );
        self::assertSame(
            20,
            $participantRepository->getParticipantsCount(
                [ParticipantRole::PatrolParticipant],
                UserStatus::cases(),
                $navigamus,
            ),
        );
        // paid PLs carry 2 PPs each
        self::assertSame(
            10,
            $participantRepository->getParticipantsCount(
                [ParticipantRole::PatrolParticipant],
                [UserStatus::Paid],
                $navigamus,
            ),
        );

        // PPs have no user of their own - the app never creates one for them
        $pp1 = $participantRepository->findOneBy(['email' => 'pp1@navigamus3027.test']);
        self::assertNotNull($pp1);
        self::assertNull($pp1->user);

        $navigamusPl = $participantRepository->findOneBy(['email' => 'pl20@navigamus3027.test']);
        self::assertNotNull($navigamusPl);
        self::assertSame('detail.contingent.without', $navigamusPl->contingent);

        $obrok = $eventRepository->findBySlug('obrok3027');
        self::assertNotNull($obrok);
        self::assertSame(
            20,
            $participantRepository->getParticipantsCount([ParticipantRole::TroopLeader], UserStatus::cases(), $obrok),
        );
        self::assertSame(
            20,
            $participantRepository->getParticipantsCount(
                [ParticipantRole::TroopParticipant],
                UserStatus::cases(),
                $obrok,
            ),
        );
        self::assertSame(
            0,
            $participantRepository->getParticipantsCount([ParticipantRole::PatrolLeader], UserStatus::cases(), $obrok),
        );

        // TPs reach paid via their leader's payment cascade - the app never creates a TP payment directly
        $obrokTp = $participantRepository->findOneBy(['email' => 'tp20@obrok3027.test']);
        self::assertNotNull($obrokTp);
        self::assertCount(0, $paymentRepository->findByParticipant($obrokTp));

        $this->runSeed();
        self::assertSame(
            20,
            $participantRepository->getParticipantsCount(
                [ParticipantRole::PatrolParticipant],
                UserStatus::cases(),
                $navigamus,
            ),
        );
    }

    public function testSeedsProgrammesWithRolesAndOpenWindow(): void
    {
        $app = $this->getTestApp();
        /** @var EventRepository $eventRepository */
        $eventRepository = $this->getService($app, EventRepository::class);
        /** @var ProgrammeRepository $programmeRepository */
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        $expectedCounts = ['navigamus3027' => 8, 'korbo3026' => 59, 'miquik3026' => 41, 'obrok3027' => 204];

        $this->runSeed();

        foreach ($expectedCounts as $slug => $count) {
            $event = $eventRepository->findBySlug($slug);
            self::assertNotNull($event);
            self::assertCount($count, $programmeRepository->findActiveForEvent($event), $slug);
            self::assertTrue($event->isProgrammeRegistrationOpen(), $slug);
        }
        foreach (['nsj3025', 'cej3026'] as $slug) {
            $event = $eventRepository->findBySlug($slug);
            self::assertNotNull($event);
            self::assertCount(0, $programmeRepository->findActiveForEvent($event), $slug);
            self::assertFalse($event->isProgrammeRegistrationOpen(), $slug);
        }

        $obrok = $eventRepository->findBySlug('obrok3027');
        self::assertNotNull($obrok);
        $obrokProgrammes = $programmeRepository->findActiveForEvent($obrok);
        foreach ($obrokProgrammes as $programme) {
            self::assertNotContains(ParticipantRole::PatrolLeader, $programme->getTargetRoles());
        }
        $targetingTroopLeader = array_filter(
            $obrokProgrammes,
            fn (Programme $programme): bool => $programme->isForRole(ParticipantRole::TroopLeader),
        );
        self::assertNotEmpty($targetingTroopLeader);

        $miquik = $eventRepository->findBySlug('miquik3026');
        self::assertNotNull($miquik);
        foreach ($programmeRepository->findActiveForEvent($miquik) as $programme) {
            self::assertSame(['ist', 'pl'], $this->sortedRoleValues($programme), $programme->name);
        }
        $roleLinksBefore = $this->countRoleLinks($eventRepository, $programmeRepository, array_keys($expectedCounts));

        $this->runSeed();

        foreach ($expectedCounts as $slug => $count) {
            $event = $eventRepository->findBySlug($slug);
            self::assertNotNull($event);
            self::assertCount($count, $programmeRepository->findActiveForEvent($event), $slug);
        }
        self::assertSame(
            $roleLinksBefore,
            $this->countRoleLinks($eventRepository, $programmeRepository, array_keys($expectedCounts)),
        );
    }

    public function testSeedsSkautImagesOnThreeOfFourObrokProgrammes(): void
    {
        $app = $this->getTestApp();
        /** @var EventRepository $eventRepository */
        $eventRepository = $this->getService($app, EventRepository::class);
        /** @var ProgrammeRepository $programmeRepository */
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);

        $this->runSeed();

        $obrok = $eventRepository->findBySlug('obrok3027');
        self::assertNotNull($obrok);
        $sourceFolder = __DIR__ . '/../../src/Application/migrations/seeds/programmes/images/';
        $sourceBytesByHash = [];
        $sourcePaths = glob($sourceFolder . 'skaut-*.png');
        self::assertIsArray($sourcePaths);
        foreach ($sourcePaths as $sourcePath) {
            $sourceBytesByHash[md5(basename($sourcePath))] = file_get_contents($sourcePath);
        }
        self::assertCount(20, $sourceBytesByHash);

        $withImage = 0;
        foreach ($programmeRepository->findActiveForEvent($obrok) as $programme) {
            if ($programme->imageFilename === null) {
                continue;
            }
            $withImage++;
            self::assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $programme->imageFilename);
            self::assertArrayHasKey($programme->imageFilename, $sourceBytesByHash);
            self::assertSame(
                $sourceBytesByHash[$programme->imageFilename],
                file_get_contents(__DIR__ . '/../../uploads/' . $programme->imageFilename),
            );
        }
        self::assertSame(153, $withImage);

        $navigamus = $eventRepository->findBySlug('navigamus3027');
        self::assertNotNull($navigamus);
        foreach ($programmeRepository->findActiveForEvent($navigamus) as $programme) {
            self::assertNull($programme->imageFilename);
        }
    }

    public function testSeedRefillsProgrammesOfEventThatHasNone(): void
    {
        $app = $this->getTestApp();
        /** @var EventRepository $eventRepository */
        $eventRepository = $this->getService($app, EventRepository::class);
        /** @var ProgrammeRepository $programmeRepository */
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);

        $this->runSeed();

        $navigamus = $eventRepository->findBySlug('navigamus3027');
        self::assertNotNull($navigamus);
        foreach ($programmeRepository->findActiveForEvent($navigamus) as $programme) {
            $programmeRepository->delete($programme);
        }
        self::assertCount(0, $programmeRepository->findActiveForEvent($navigamus));

        $this->runSeed();

        self::assertCount(8, $programmeRepository->findActiveForEvent($navigamus));
    }

    public function testSeedRefillKeepsEventDays(): void
    {
        $app = $this->getTestApp();
        /** @var EventRepository $eventRepository */
        $eventRepository = $this->getService($app, EventRepository::class);
        /** @var ProgrammeRepository $programmeRepository */
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);

        $this->runSeed();

        $korbo = $eventRepository->findBySlug('korbo3026');
        self::assertNotNull($korbo);
        foreach ($programmeRepository->findActiveForEvent($korbo) as $programme) {
            $programmeRepository->delete($programme);
        }
        $korbo->endDay = DateTimeUtils::getDateTime('2026-11-15');
        $eventRepository->persist($korbo);

        $this->runSeed();

        $korboAfter = $eventRepository->findBySlug('korbo3026');
        self::assertNotNull($korboAfter);
        self::assertSame('2026-11-15', $korboAfter->endDay->format('Y-m-d'));
        self::assertCount(59, $programmeRepository->findActiveForEvent($korboAfter));
    }

    public function testSeedDoesNotRefillEventWhoseProgrammesAreAllSoftDeleted(): void
    {
        $app = $this->getTestApp();
        /** @var EventRepository $eventRepository */
        $eventRepository = $this->getService($app, EventRepository::class);
        /** @var ProgrammeRepository $programmeRepository */
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);

        $this->runSeed();

        $navigamus = $eventRepository->findBySlug('navigamus3027');
        self::assertNotNull($navigamus);
        foreach ($programmeRepository->findActiveForEvent($navigamus) as $programme) {
            $programme->deletedAt = DateTimeUtils::getDateTime();
            $programmeRepository->persist($programme);
        }
        self::assertCount(0, $programmeRepository->findActiveForEvent($navigamus));

        $this->runSeed();

        self::assertCount(0, $programmeRepository->findActiveForEvent($navigamus));
    }

    public function testSeedsProgrammeSections(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        $sectionRepository = $this->getService($app, ProgrammeSectionRepository::class);
        $expectedSections = [
            'navigamus3027' => ['Doprovodný program' => true, 'Ceremoniály' => false],
            'korbo3026' => [
                'Pohybová' => false,
                'Tvořivá' => false,
                'Kulturní' => false,
                'Přednáška' => false,
                'Debata' => false,
                'Jiné' => false,
            ],
            'miquik3026' => [
                'Úvodní přednáška' => false,
                '1. sekce' => true,
                '2. sekce' => true,
                '3. sekce' => true,
                'Závěrečná přednáška' => false,
            ],
            'obrok3027' => [
                'Účastníci' => false,
                'V areálu' => false,
                'VaPro' => true,
                'VýRo' => true,
                'Semináře' => true,
                'RoPro' => true,
                'Inspiromat' => true,
            ],
        ];

        $this->runSeed();

        foreach (self::SEEDED_SLUGS as $slug) {
            $event = $eventRepository->findBySlug($slug);
            self::assertNotNull($event);
            self::assertSame(
                $expectedSections[$slug] ?? [],
                $this->sectionExclusivityByName($sectionRepository->findForEvent($event)),
                $slug,
            );
            foreach ($programmeRepository->findActiveForEvent($event) as $programme) {
                self::assertSame($event->id, $programme->section->event->id, $programme->name);
            }
        }

        $obrok = $eventRepository->findBySlug('obrok3027');
        self::assertNotNull($obrok);
        $obrokCounts = [];
        foreach ($sectionRepository->findForEvent($obrok) as $section) {
            $obrokCounts[$section->name] = $sectionRepository->countProgrammes($section);
        }
        self::assertSame(
            [
                'Účastníci' => 15,
                'V areálu' => 135,
                'VaPro' => 12,
                'VýRo' => 18,
                'Semináře' => 11,
                'RoPro' => 9,
                'Inspiromat' => 4,
            ],
            $obrokCounts,
        );
        $sectionCountBefore = $this->countSections($eventRepository, $sectionRepository);

        $this->runSeed();

        self::assertSame($sectionCountBefore, $this->countSections($eventRepository, $sectionRepository));
    }

    public function testReseedMovesProgrammesOutOfLegacyProgramSection(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        $sectionRepository = $this->getService($app, ProgrammeSectionRepository::class);

        $this->runSeed();

        $navigamus = $eventRepository->findBySlug('navigamus3027');
        self::assertNotNull($navigamus);
        $legacySection = $this->createProgrammeSection($app->getContainer(), $navigamus, 'Program');
        foreach ($programmeRepository->findActiveForEvent($navigamus) as $programme) {
            $programme->section = $legacySection;
            $programme->start = DateTimeUtils::getDateTime($programme->start->format('Y-m-d H:i:s') . ' +1 day');
            $programme->end = DateTimeUtils::getDateTime($programme->end->format('Y-m-d H:i:s') . ' +1 day');
            $programmeRepository->persist($programme);
        }
        foreach ($sectionRepository->findForEvent($navigamus) as $section) {
            if ($section->id !== $legacySection->id) {
                $sectionRepository->delete($section);
            }
        }

        $this->runSeed();

        $sections = $sectionRepository->findForEvent($navigamus);
        self::assertSame(
            ['Doprovodný program' => true, 'Ceremoniály' => false],
            $this->sectionExclusivityByName($sections),
        );
        self::assertSame(8, $sectionRepository->countProgrammes($sections[0]));
        self::assertCount(8, $programmeRepository->findActiveForEvent($navigamus));
    }

    public function testReseedRestoresSectionExclusivityOrderAndSubtitle(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $sectionRepository = $this->getService($app, ProgrammeSectionRepository::class);

        $this->runSeed();

        $navigamus = $eventRepository->findBySlug('navigamus3027');
        self::assertNotNull($navigamus);
        $section = $sectionRepository->findForEvent($navigamus)[0];
        self::assertSame('Doprovodný program', $section->name);
        $section->isExclusive = false;
        $section->sortOrder = 9;
        $section->subtitle = 'manual subtitle';
        $sectionRepository->persist($section);

        $this->runSeed();

        $restored = $sectionRepository->get($section->id);
        self::assertInstanceOf(ProgrammeSection::class, $restored);
        self::assertTrue($restored->isExclusive);
        self::assertSame(0, $restored->sortOrder);
        self::assertNull($restored->subtitle);
    }

    /**
     * @param list<ProgrammeSection> $sections
     * @return array<string, bool>
     */
    private function sectionExclusivityByName(array $sections): array
    {
        $exclusivityByName = [];
        foreach ($sections as $section) {
            $exclusivityByName[$section->name] = $section->isExclusive;
        }

        return $exclusivityByName;
    }

    private function countSections(EventRepository $eventRepository, ProgrammeSectionRepository $sectionRepository): int
    {
        $total = 0;
        foreach (self::SEEDED_SLUGS as $slug) {
            $event = $eventRepository->findBySlug($slug);
            self::assertNotNull($event);
            $total += count($sectionRepository->findForEvent($event));
        }

        return $total;
    }

    /**
     * @return list<string>
     */
    private function sortedRoleValues(Programme $programme): array
    {
        $values = array_map(fn (ParticipantRole $role): string => $role->value, $programme->getTargetRoles());
        sort($values);

        return $values;
    }

    /**
     * @param list<string|int> $slugs
     */
    private function countRoleLinks(
        EventRepository $eventRepository,
        ProgrammeRepository $programmeRepository,
        array $slugs,
    ): int {
        $total = 0;
        foreach ($slugs as $slug) {
            $event = $eventRepository->findBySlug((string)$slug);
            self::assertNotNull($event);
            foreach ($programmeRepository->findActiveForEvent($event) as $programme) {
                $total += count($programme->getTargetRoles());
            }
        }

        return $total;
    }

    private function runSeed(): void
    {
        // tripwire mirrors AppTestCase::provideCleanDatabase() - refuse to run phinx against
        // anything but the per-test sqlite database
        if ($_ENV['DB_TYPE'] !== 'sqlite') {
            throw new LogicException('Seed test must run on sqlite, refusing to touch a real database.');
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
