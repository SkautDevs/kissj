<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\EventRepository;
use kissj\Export\ExportService;
use kissj\Participant\Ist\IstRepository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRole;
use kissj\PdfGenerator\PdfGenerator;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeEnrolmentRepository;
use kissj\Programme\ProgrammeRepository;
use kissj\Programme\ProgrammeSection;
use kissj\Programme\ProgrammeSectionRepository;
use kissj\Programme\ProgrammeService;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class ProgrammeExportTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-event-slug';

    public function testAttendanceHtmlListsParticipantsAndSkipsAutoenrolled(): void
    {
        $app = $this->getTestApp();
        $this->openProgrammeEnrolmentWindow($app);
        $service = $this->getService($app, ProgrammeService::class);
        $pdfGenerator = $this->getService($app, PdfGenerator::class);
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        $enrolmentRepository = $this->getService($app, ProgrammeEnrolmentRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->getOneBy(['slug' => 'test-event-slug']);

        $workshop = $this->createProgramme($app, 'print workshop', null, 5);
        $this->createProgramme($app, 'print ceremony', null, 1, '2027-06-05 08:00', '2027-06-05 09:00', null, true);
        $ist = $this->createPaidIstWithEmail($app, 'print-ist@example.com');
        $ist->firstName = 'Printed';
        $ist->lastName = 'Person';
        $this->getService($app, IstRepository::class)->persist($ist);
        self::assertNull($service->enrol($ist, $workshop->id)->error);

        $sheets = [];
        foreach ($programmeRepository->findActiveForEvent($event) as $programme) {
            if ($programme->isAutoenrolled) {
                continue;
            }
            $sheets[] = [
                'programme' => $programme,
                'enrolments' => $enrolmentRepository->findActiveForProgramme($programme),
            ];
        }
        $html = $pdfGenerator->buildProgrammeAttendanceHtml($event, $sheets);

        self::assertStringContainsString('print workshop', $html);
        self::assertStringContainsString('Printed Person', $html);
        self::assertStringNotContainsString('print ceremony', $html);
    }

    public function testProgrammeCsvContainsActiveEnrolmentsOnly(): void
    {
        $app = $this->getTestApp();
        $this->openProgrammeEnrolmentWindow($app);
        $service = $this->getService($app, ProgrammeService::class);
        $exportService = $this->getService($app, ExportService::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->getOneBy(['slug' => 'test-event-slug']);

        $workshops = $this->createProgrammeSection($app->getContainer(), $event, 'workshops', true);
        $workshop = $this->createProgramme($app, 'csv workshop', $workshops, 5);
        $this->createProgramme($app, 'csv ceremony', null, 1, '2027-06-05 08:00', '2027-06-05 09:00', null, true);
        $active = $this->createPaidIstWithEmail($app, 'csv-active@example.com');
        $gone = $this->createPaidIstWithEmail($app, 'csv-gone@example.com');
        self::assertNull($service->enrol($active, $workshop->id)->error);
        self::assertNull($service->enrol($gone, $workshop->id)->error);
        self::assertNull($service->withdraw($gone, $workshop->id)->error);

        $rows = $exportService->programmeEnrolmentsToCSV($event);

        self::assertCount(2, $rows); // header + 1 active enrolment
        self::assertSame('section', $rows[0][1]);
        self::assertSame('csv workshop', $rows[1][0]);
        self::assertSame('workshops', $rows[1][1]);
        self::assertStringContainsString('csv-active@example.com', implode(',', $rows[1]));
        self::assertStringNotContainsString('csv-gone@example.com', (string)json_encode($rows));
        self::assertStringNotContainsString('csv ceremony', (string)json_encode($rows));
    }

    public function testSectionSubtitleIsShownInCsvAndAttendance(): void
    {
        $app = $this->getTestApp();
        $this->openProgrammeEnrolmentWindow($app);
        $service = $this->getService($app, ProgrammeService::class);
        $event = $this->getService($app, EventRepository::class)->getOneBy(['slug' => self::TEST_EVENT_SLUG]);
        $section = $this->createProgrammeSection($app->getContainer(), $event, 'Vapro');
        $section->subtitle = '1. blok';
        $this->getService($app, ProgrammeSectionRepository::class)->persist($section);
        $programme = $this->createProgramme($app, 'subtitled workshop', $section, 5);
        self::assertNull($service->enrol($this->createPaidIstWithEmail($app, 'csv-sub@example.com'), $programme->id)
            ->error);

        $rows = $this->getService($app, ExportService::class)->programmeEnrolmentsToCSV($event);
        $html = $this->getService($app, PdfGenerator::class)->buildProgrammeAttendanceHtml($event, [[
            'programme' => $programme,
            'enrolments' => $this->getService($app, ProgrammeEnrolmentRepository::class)
                ->findActiveForProgramme($programme),
        ]]);

        self::assertSame(['subtitled workshop', 'Vapro (1. blok)'], array_slice($rows[1], 0, 2));
        self::assertStringContainsString('(Vapro (1. blok))', $html);
    }

    public function testGroupedActiveEnrolmentsExcludeSoftDeletedAndForeignProgrammes(): void
    {
        $app = $this->getTestApp();
        $this->openProgrammeEnrolmentWindow($app);
        $service = $this->getService($app, ProgrammeService::class);
        $enrolmentRepository = $this->getService($app, ProgrammeEnrolmentRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->getOneBy(['slug' => 'test-event-slug']);

        $workshop = $this->createProgramme($app, 'grouped workshop', null, 5);
        $empty = $this->createProgramme($app, 'grouped empty', null, 5);
        $active = $this->createPaidIstWithEmail($app, 'grouped-active@example.com');
        $gone = $this->createPaidIstWithEmail($app, 'grouped-gone@example.com');
        self::assertNull($service->enrol($active, $workshop->id)->error);
        self::assertNull($service->enrol($gone, $workshop->id)->error);
        self::assertNull($service->withdraw($gone, $workshop->id)->error);

        $grouped = $enrolmentRepository->findActiveForEventGroupedByProgramme($event);

        // exactly the one active enrolment under its programme, soft-deleted one excluded
        self::assertArrayHasKey($workshop->id, $grouped);
        self::assertCount(1, $grouped[$workshop->id]);
        self::assertSame($active->id, $grouped[$workshop->id][0]->participant->id);
        // a programme with no active enrolments is absent from the grouping
        self::assertArrayNotHasKey($empty->id, $grouped);
    }

    /**
     * @param App<ContainerInterface> $app
     * @param list<ParticipantRole>|null $roles
     */
    private function createProgramme(
        App $app,
        string $name,
        ?ProgrammeSection $section,
        int $capacity,
        string $start = '2027-06-05 10:00',
        string $end = '2027-06-05 12:00',
        ?array $roles = null,
        bool $isAutoenrolled = false,
    ): Programme {
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);

        $programme = new Programme();
        $programme->name = $name;
        $programme->description = 'description';
        $programme->programmePlace = null;
        $programme->start = DateTimeUtils::getDateTime($start);
        $programme->end = DateTimeUtils::getDateTime($end);
        $programme->capacity = $capacity;
        $programme->isAutoenrolled = $isAutoenrolled;
        $programme->event = $eventRepository->getOneBy(['slug' => self::TEST_EVENT_SLUG]);
        $programme->section = $section
            ?? $this->createProgrammeSection($app->getContainer(), $programme->event, 'Program');
        $programme->setTargetRoles(
            $roles ?? [ParticipantRole::Ist, ParticipantRole::TroopLeader, ParticipantRole::TroopParticipant],
        );
        $programmeRepository->persist($programme);

        return $programme;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createPaidIstWithEmail(App $app, string $email): Participant
    {
        $user = $this->registerUser($app, $email);
        $userService = $this->getService($app, UserService::class);
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
    private function registerUser(App $app, string $email): User
    {
        $userService = $this->getService($app, UserService::class);
        $eventRepository = $this->getService($app, EventRepository::class);

        $event = $eventRepository->findBySlug(self::TEST_EVENT_SLUG);
        if ($event === null) {
            $event = $eventRepository->get(1);
        }

        return $userService->registerEmailUser($email, $event);
    }
}
