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
use kissj\Programme\ProgrammeRegistrationRepository;
use kissj\Programme\ProgrammeRepository;
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

    public function testAttendanceHtmlListsParticipantsAndSkipsPreregistered(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeService::class);
        $pdfGenerator = $this->getService($app, PdfGenerator::class);
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        $registrationRepository = $this->getService($app, ProgrammeRegistrationRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->getOneBy(['slug' => 'test-event-slug']);

        $workshop = $this->createProgramme($app, 'print workshop', null, 5);
        $this->createProgramme($app, 'print ceremony', null, 1, '2027-06-05 08:00', '2027-06-05 09:00', null, true);
        $ist = $this->createPaidIstWithEmail($app, 'print-ist@example.com');
        $ist->firstName = 'Printed';
        $ist->lastName = 'Person';
        $this->getService($app, IstRepository::class)->persist($ist);
        self::assertTrue($service->register($ist, $workshop->id)->success);

        $sheets = [];
        foreach ($programmeRepository->findActiveForEvent($event) as $programme) {
            if ($programme->isPreregistered) {
                continue;
            }
            $sheets[] = [
                'programme' => $programme,
                'registrations' => $registrationRepository->findActiveForProgramme($programme),
            ];
        }
        $html = $pdfGenerator->buildProgrammeAttendanceHtml($event, $sheets);

        self::assertStringContainsString('print workshop', $html);
        self::assertStringContainsString('Printed Person', $html);
        self::assertStringNotContainsString('print ceremony', $html);
    }

    public function testProgrammeCsvContainsActiveRegistrationsOnly(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeService::class);
        $exportService = $this->getService($app, ExportService::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->getOneBy(['slug' => 'test-event-slug']);

        $workshop = $this->createProgramme($app, 'csv workshop', 'workshops', 5);
        $this->createProgramme($app, 'csv ceremony', null, 1, '2027-06-05 08:00', '2027-06-05 09:00', null, true);
        $active = $this->createPaidIstWithEmail($app, 'csv-active@example.com');
        $gone = $this->createPaidIstWithEmail($app, 'csv-gone@example.com');
        self::assertTrue($service->register($active, $workshop->id)->success);
        self::assertTrue($service->register($gone, $workshop->id)->success);
        self::assertTrue($service->unregister($gone, $workshop->id)->success);

        $rows = $exportService->programmeRegistrationsToCSV($event);

        self::assertCount(2, $rows); // header + 1 active registration
        self::assertSame('csv workshop', $rows[1][0]);
        self::assertStringContainsString('csv-active@example.com', implode(',', $rows[1]));
        self::assertStringNotContainsString('csv-gone@example.com', (string)json_encode($rows));
        self::assertStringNotContainsString('csv ceremony', (string)json_encode($rows));
    }

    public function testGroupedActiveRegistrationsExcludeSoftDeletedAndForeignProgrammes(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeService::class);
        $registrationRepository = $this->getService($app, ProgrammeRegistrationRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->getOneBy(['slug' => 'test-event-slug']);

        $workshop = $this->createProgramme($app, 'grouped workshop', null, 5);
        $empty = $this->createProgramme($app, 'grouped empty', null, 5);
        $active = $this->createPaidIstWithEmail($app, 'grouped-active@example.com');
        $gone = $this->createPaidIstWithEmail($app, 'grouped-gone@example.com');
        self::assertTrue($service->register($active, $workshop->id)->success);
        self::assertTrue($service->register($gone, $workshop->id)->success);
        self::assertTrue($service->unregister($gone, $workshop->id)->success);

        $grouped = $registrationRepository->findActiveForEventGroupedByProgramme($event);

        // exactly the one active registration under its programme, soft-deleted one excluded
        self::assertArrayHasKey($workshop->id, $grouped);
        self::assertCount(1, $grouped[$workshop->id]);
        self::assertSame($active->id, $grouped[$workshop->id][0]->participant->id);
        // a programme with no active registrations is absent from the grouping
        self::assertArrayNotHasKey($empty->id, $grouped);
    }

    /**
     * @param App<ContainerInterface> $app
     * @param list<ParticipantRole>|null $roles
     */
    private function createProgramme(
        App $app,
        string $name,
        ?string $groupName,
        int $capacity,
        string $start = '2027-06-05 10:00',
        string $end = '2027-06-05 12:00',
        ?array $roles = null,
        bool $isPreregistered = false,
    ): Programme {
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);

        $programme = new Programme();
        $programme->name = $name;
        $programme->description = 'description';
        $programme->place = 'somewhere';
        $programme->imageFilename = null;
        $programme->start = DateTimeUtils::getDateTime($start);
        $programme->end = DateTimeUtils::getDateTime($end);
        $programme->capacity = $capacity;
        $programme->isPreregistered = $isPreregistered;
        $programme->exclusiveGroupName = $groupName;
        $programme->event = $eventRepository->getOneBy(['slug' => self::TEST_EVENT_SLUG]);
        $programme->setTargetRoles($roles ?? [ParticipantRole::Ist, ParticipantRole::TroopLeader, ParticipantRole::TroopParticipant]);
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
