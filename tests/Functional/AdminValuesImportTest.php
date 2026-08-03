<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Event\EventScope;
use kissj\Event\EventType\Cej\EventTypeCej;
use kissj\Participant\Admin\AdminService;
use kissj\Participant\Admin\AdminValuesImportProblem;
use kissj\Participant\Admin\AdminValuesImportProblemType;
use kissj\Participant\ParticipantRepository;
use kissj\Translation\CurrentTranslator;
use kissj\User\User;
use kissj\User\UserLoginType;
use kissj\User\UserRepository;
use kissj\User\UserRole;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class AdminValuesImportTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-slug';
    private const string BASE_URL = '/v2/event/' . self::TEST_EVENT_SLUG;
    private const string CSV_HEADER = 'firstName,lastName,subcamp,internalUniqueId,internalCommonId';

    public function testDryRunReportsWithoutWriting(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $this->setEventType($container, 'cej', self::TEST_EVENT_SLUG);

        try {
            [$adminService, $participantRepository, $event] = $this->getImportServices($app);
            $suffix = bin2hex(random_bytes(4));
            $anna = $this->createPaidIst($container, 'Anna', 'Import' . $suffix);

            $csv = self::CSV_HEADER . "\n" . 'Anna,Import' . $suffix . ',Théba,U-' . $suffix . ',G-1';
            $report = $adminService->importAdminValues($event, $csv, false);

            self::assertSame(1, $report->okCount);
            self::assertSame([], $report->problems);

            $reloaded = $participantRepository->getParticipantById($anna->id, $event);
            self::assertNull($reloaded->subcamp);
            self::assertNull($reloaded->internalUniqueId);
        } finally {
            $this->resetEventToDefault($container, self::TEST_EVENT_SLUG);
        }
    }

    public function testApplyWritesValuesAndReimportIsIdempotent(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $this->setEventType($container, 'cej', self::TEST_EVENT_SLUG);

        try {
            [$adminService, $participantRepository, $event] = $this->getImportServices($app);
            $suffix = bin2hex(random_bytes(4));
            $anna = $this->createPaidIst($container, 'Anna', 'Apply' . $suffix);
            $bela = $this->createPaidIst($container, 'Béla', 'Apply' . $suffix);

            $csv = self::CSV_HEADER . "\n"
                . 'Anna,Apply' . $suffix . ',Théba,U-A-' . $suffix . ',G-1' . "\n"
                . 'Béla,Apply' . $suffix . ',Spárta,U-B-' . $suffix . ',G-1';

            $report = $adminService->importAdminValues($event, $csv, true);
            self::assertSame(2, $report->okCount);
            self::assertSame([], $report->problems);

            $annaReloaded = $participantRepository->getParticipantById($anna->id, $event);
            self::assertSame(EventTypeCej::SUBCAMP_THEBA, $annaReloaded->subcamp);
            self::assertSame('U-A-' . $suffix, $annaReloaded->internalUniqueId);
            self::assertSame('G-1', $annaReloaded->internalCommonId);
            $belaReloaded = $participantRepository->getParticipantById($bela->id, $event);
            self::assertSame(EventTypeCej::SUBCAMP_SPARTA, $belaReloaded->subcamp);

            // re-import of the same sheet stays clean
            $reportAgain = $adminService->importAdminValues($event, $csv, true);
            self::assertSame(2, $reportAgain->okCount);
            self::assertSame([], $reportAgain->problems);
        } finally {
            $this->resetEventToDefault($container, self::TEST_EVENT_SLUG);
        }
    }

    public function testProblemRowsAreRefusedAndReported(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $this->setEventType($container, 'cej', self::TEST_EVENT_SLUG);

        try {
            [$adminService, $participantRepository, $event] = $this->getImportServices($app);
            $suffix = bin2hex(random_bytes(4));
            // two participants with the same name -> ambiguous
            $this->createPaidIst($container, 'Jan', 'Dvojnik' . $suffix);
            $this->createPaidIst($container, 'Jan', 'Dvojnik' . $suffix);
            // holder of an already-assigned unique ID
            $holder = $this->createPaidIst($container, 'Drzi', 'Idcko' . $suffix);
            $holder->internalUniqueId = 'TAKEN-' . $suffix;
            $participantRepository->persist($holder);
            $victim = $this->createPaidIst($container, 'Obet', 'Kolize' . $suffix);
            $ok = $this->createPaidIst($container, 'Vse', 'Vporadku' . $suffix);

            $csv = self::CSV_HEADER . "\n"
                . 'Jan,Dvojnik' . $suffix . ',Théba,A-' . $suffix . ',G' . "\n"      // ambiguous
                . 'Neexistuje,Nikde' . $suffix . ',Théba,B-' . $suffix . ',G' . "\n"  // no match
                . 'Vse,Vporadku' . $suffix . ',Atlantis,C-' . $suffix . ',G' . "\n"   // unknown subcamp
                . 'Obet,Kolize' . $suffix . ',Théba,TAKEN-' . $suffix . ',G' . "\n"   // id taken by holder
                . 'jen,dva,sloupce' . "\n"                                            // invalid row (3 cells)
                . 'Vse,Vporadku' . $suffix . ',Théba,D-' . $suffix . ',G-OK';         // valid

            $report = $adminService->importAdminValues($event, $csv, true);

            self::assertSame(1, $report->okCount);
            self::assertSame(
                [
                    AdminValuesImportProblemType::AmbiguousName,
                    AdminValuesImportProblemType::NoMatch,
                    AdminValuesImportProblemType::UnknownSubcamp,
                    AdminValuesImportProblemType::UniqueIdTakenByOther,
                    AdminValuesImportProblemType::InvalidRow,
                ],
                $this->getProblemTypes($report->problems),
            );
            self::assertSame(2, $report->problems[0]->lineNumber);
            self::assertSame('Jan Dvojnik' . $suffix, $report->problems[0]->name);

            // the valid row applied, the victim row did not
            $okReloaded = $participantRepository->getParticipantById($ok->id, $event);
            self::assertSame('D-' . $suffix, $okReloaded->internalUniqueId);
            $victimReloaded = $participantRepository->getParticipantById($victim->id, $event);
            self::assertNull($victimReloaded->internalUniqueId);
        } finally {
            $this->resetEventToDefault($container, self::TEST_EVENT_SLUG);
        }
    }

    public function testUniqueIdHeldByOpenParticipantIsRefused(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $this->setEventType($container, 'cej', self::TEST_EVENT_SLUG);

        try {
            [$adminService, $participantRepository, $event] = $this->getImportServices($app);
            $suffix = bin2hex(random_bytes(4));

            // an open registration is outside the matching pool, yet its ID must still block others
            $openHolder = $this->createOpenIst($container, 'Otevreny', 'Drzitel' . $suffix);
            $openHolder->internalUniqueId = 'OPEN-' . $suffix;
            $participantRepository->persist($openHolder);

            $applicant = $this->createPaidIst($container, 'Zadatel', 'Placeny' . $suffix);

            $csv = self::CSV_HEADER . "\n"
                . 'Zadatel,Placeny' . $suffix . ',Théba,OPEN-' . $suffix . ',G';
            $report = $adminService->importAdminValues($event, $csv, true);

            self::assertSame(0, $report->okCount);
            self::assertSame(
                [AdminValuesImportProblemType::UniqueIdTakenByOther],
                $this->getProblemTypes($report->problems),
            );

            $applicantReloaded = $participantRepository->getParticipantById($applicant->id, $event);
            self::assertNull($applicantReloaded->internalUniqueId);
        } finally {
            $this->resetEventToDefault($container, self::TEST_EVENT_SLUG);
        }
    }

    public function testDuplicateIdAndNameInsideCsvAreRefused(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $this->setEventType($container, 'cej', self::TEST_EVENT_SLUG);

        try {
            [$adminService, , $event] = $this->getImportServices($app);
            $suffix = bin2hex(random_bytes(4));
            $this->createPaidIst($container, 'Prvni', 'Radek' . $suffix);
            $this->createPaidIst($container, 'Druhy', 'Radek' . $suffix);

            $csv = self::CSV_HEADER . "\n"
                . 'Prvni,Radek' . $suffix . ',Théba,DUP-' . $suffix . ',G' . "\n"
                . 'Druhy,Radek' . $suffix . ',Théba,DUP-' . $suffix . ',G' . "\n"   // duplicate id in csv
                . 'Prvni,Radek' . $suffix . ',Spárta,E-' . $suffix . ',G';          // duplicate name in csv

            $report = $adminService->importAdminValues($event, $csv, false);

            self::assertSame(1, $report->okCount);
            self::assertSame(
                [
                    AdminValuesImportProblemType::DuplicateUniqueIdInCsv,
                    AdminValuesImportProblemType::DuplicateNameInCsv,
                ],
                $this->getProblemTypes($report->problems),
            );
        } finally {
            $this->resetEventToDefault($container, self::TEST_EVENT_SLUG);
        }
    }

    public function testInvalidHeaderRefusesWholeImport(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $this->setEventType($container, 'cej', self::TEST_EVENT_SLUG);

        try {
            [$adminService, , $event] = $this->getImportServices($app);

            $report = $adminService->importAdminValues($event, "foo,bar\nAnna,Import,Théba,U,G", true);

            self::assertSame(0, $report->okCount);
            self::assertCount(1, $report->problems);
            self::assertSame(AdminValuesImportProblemType::InvalidHeader, $report->problems[0]->type);
            self::assertSame(1, $report->problems[0]->lineNumber);
        } finally {
            $this->resetEventToDefault($container, self::TEST_EVENT_SLUG);
        }
    }

    public function testMatchingTrimsAndCaseFoldsButKeepsDiacritics(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $this->setEventType($container, 'cej', self::TEST_EVENT_SLUG);

        try {
            [$adminService, $participantRepository, $event] = $this->getImportServices($app);
            $suffix = bin2hex(random_bytes(4));
            $zofie = $this->createPaidIst($container, 'Žofie', 'Hačková' . $suffix);

            // spaces and case differ, diacritics match
            $csv = self::CSV_HEADER . "\n" . ' žofie , hačková' . $suffix . ' ,Théba,F-' . $suffix . ',G';
            $report = $adminService->importAdminValues($event, $csv, true);
            self::assertSame(1, $report->okCount);
            self::assertSame([], $report->problems);
            $reloaded = $participantRepository->getParticipantById($zofie->id, $event);
            self::assertSame('F-' . $suffix, $reloaded->internalUniqueId);

            // ascii-stripped name must NOT match (diacritics preserved)
            $csvAscii = self::CSV_HEADER . "\n" . 'Zofie,Hackova' . $suffix . ',Théba,H-' . $suffix . ',G';
            $reportAscii = $adminService->importAdminValues($event, $csvAscii, false);
            self::assertSame(0, $reportAscii->okCount);
            self::assertSame(AdminValuesImportProblemType::NoMatch, $reportAscii->problems[0]->type);
        } finally {
            $this->resetEventToDefault($container, self::TEST_EVENT_SLUG);
        }
    }

    public function testEmptyCellsClearStoredValues(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $this->setEventType($container, 'cej', self::TEST_EVENT_SLUG);

        try {
            [$adminService, $participantRepository, $event] = $this->getImportServices($app);
            $suffix = bin2hex(random_bytes(4));
            $participant = $this->createPaidIst($container, 'Vymaz', 'Hodnoty' . $suffix);
            $participant->subcamp = EventTypeCej::SUBCAMP_ATHENS;
            $participant->internalUniqueId = 'OLD-' . $suffix;
            $participant->internalCommonId = 'G-OLD';
            $participantRepository->persist($participant);

            $csv = self::CSV_HEADER . "\n" . 'Vymaz,Hodnoty' . $suffix . ',,,';
            $report = $adminService->importAdminValues($event, $csv, true);
            self::assertSame(1, $report->okCount);
            self::assertSame([], $report->problems);

            $reloaded = $participantRepository->getParticipantById($participant->id, $event);
            self::assertNull($reloaded->subcamp);
            self::assertNull($reloaded->internalUniqueId);
            self::assertNull($reloaded->internalCommonId);
        } finally {
            $this->resetEventToDefault($container, self::TEST_EVENT_SLUG);
        }
    }

    public function testImportPageDryRunThenApplyOverHttp(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $this->setEventType($container, 'cej', self::TEST_EVENT_SLUG);

        try {
            $participantRepository = $this->getService($app, ParticipantRepository::class);
            $eventRepository = $this->getService($app, EventRepository::class);
            $event = $this->getTestSlugEvent($eventRepository);

            $adminUser = $this->createEventAdmin($container);
            $_SESSION['user'] = ['id' => $adminUser->id];

            $suffix = bin2hex(random_bytes(4));
            $participant = $this->createPaidIst($container, 'Http', 'Import' . $suffix);
            $csv = self::CSV_HEADER . "\n" . 'Http,Import' . $suffix . ',Théba,U-H-' . $suffix . ',G-H';

            $dashboardResponse = $this->getTestApp(false)->handle($this->createRequest(
                self::BASE_URL . '/admin/dashboard',
            ));
            self::assertSame(200, $dashboardResponse->getStatusCode());
            self::assertStringContainsString('/admin/adminValues', (string)$dashboardResponse->getBody());

            $pageResponse = $this->getTestApp(false)->handle($this->createRequest(
                self::BASE_URL . '/admin/adminValues',
            ));
            self::assertSame(200, $pageResponse->getStatusCode());
            self::assertStringContainsString('name="csv"', (string)$pageResponse->getBody());

            // the dry run also renders the problem block for the unmatched row
            $csvWithProblem = $csv . "\n" . 'Nikdo,Nikde' . $suffix . ',Théba,U-X-' . $suffix . ',G-X';
            $dryRunResponse = $this->getTestApp(false)->handle($this->createRequest(
                self::BASE_URL . '/admin/adminValues',
                'POST',
                ['csv' => $csvWithProblem, 'action' => 'dryRun'],
            ));
            self::assertSame(200, $dryRunResponse->getStatusCode());
            $dryRunBody = (string)$dryRunResponse->getBody();
            self::assertStringContainsString('Nikdo Nikde' . $suffix, $dryRunBody);
            self::assertStringContainsString('no participant matches', $dryRunBody);
            $notApplied = $participantRepository->getParticipantById($participant->id, $event);
            self::assertNull($notApplied->internalUniqueId);

            $applyResponse = $this->getTestApp(false)->handle($this->createRequest(
                self::BASE_URL . '/admin/adminValues',
                'POST',
                ['csv' => $csv, 'action' => 'apply'],
            ));
            self::assertSame(200, $applyResponse->getStatusCode());
            $applied = $participantRepository->getParticipantById($participant->id, $event);
            self::assertSame('U-H-' . $suffix, $applied->internalUniqueId);
        } finally {
            $this->resetEventToDefault($container, self::TEST_EVENT_SLUG);
        }
    }

    public function testImportPageRedirectsAwayWhenEventHasNoSubcamps(): void
    {
        $app = $this->getTestApp();
        // event 4 stays 'default' - no subcamps
        $adminUser = $this->createEventAdmin($app->getContainer());
        $_SESSION['user'] = ['id' => $adminUser->id];

        $dashboardResponse = $this->getTestApp(false)->handle($this->createRequest(
            self::BASE_URL . '/admin/dashboard',
        ));
        self::assertSame(200, $dashboardResponse->getStatusCode());
        self::assertStringNotContainsString('/admin/adminValues', (string)$dashboardResponse->getBody());

        $response = $this->getTestApp(false)->handle($this->createRequest(
            self::BASE_URL . '/admin/adminValues',
        ));
        self::assertSame(302, $response->getStatusCode());
    }

    private function createEventAdmin(ContainerInterface $container): User
    {
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);

        $user = new User();
        $user->event = $this->getTestSlugEvent($eventRepository);
        $user->role = UserRole::Admin;
        $user->email = 'admin-values-import-admin-' . bin2hex(random_bytes(6)) . '@example.com';
        $user->loginType = UserLoginType::Email;
        $user->status = UserStatus::Open;
        $userRepository->persist($user);

        return $user;
    }

    /**
     * @param list<AdminValuesImportProblem> $problems
     * @return list<AdminValuesImportProblemType>
     */
    private function getProblemTypes(array $problems): array
    {
        return array_map(
            static fn (AdminValuesImportProblem $problem): AdminValuesImportProblemType => $problem->type,
            $problems,
        );
    }

    /**
     * @param App<ContainerInterface> $app
     * @return array{AdminService, ParticipantRepository, Event}
     */
    private function getImportServices(App $app): array
    {
        $adminService = $this->getService($app, AdminService::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $this->getTestSlugEvent($eventRepository);

        // subcamp names are matched through the translator - middleware scopes it per request, here we do it by hand
        $this->getService($app, EventScope::class)->apply($event, 'http://localhost');
        $this->getService($app, CurrentTranslator::class)->setLocale('en');

        return [$adminService, $participantRepository, $event];
    }
}
