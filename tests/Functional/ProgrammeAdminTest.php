<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeRegistration;
use kissj\Programme\ProgrammeRegistrationRepository;
use kissj\Programme\ProgrammeRepository;
use kissj\Programme\ProgrammeSection;
use kissj\Programme\ProgrammeService;
use kissj\User\LoginToken;
use kissj\User\LoginTokenRepository;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserRole;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Slim\App;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tests\AppTestCase;

class ProgrammeAdminTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-event-slug';

    public function testAdminCreatesEditsAndDeletesProgramme(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'programme-admin@example.com');
        $workshops = $this->createProgrammeSection($app->getContainer(), $this->getEvent($app), 'workshops', true);
        $games = $this->createProgrammeSection($app->getContainer(), $this->getEvent($app), 'games');

        $uniqueName = 'created via admin ' . bin2hex(random_bytes(4));
        $responseCreate = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes/new',
            'POST',
            [
                'name' => $uniqueName,
                'description' => 'desc',
                'place' => 'tent A',
                'start' => '2027-06-05T10:00',
                'end' => '2027-06-05T12:00',
                'capacity' => '15',
                'sectionId' => (string)$workshops->id,
                'roles' => ['ist', 'tl'],
            ],
        ));
        self::assertSame(302, $responseCreate->getStatusCode());

        // the overview and create-form templates are rendered here (same already-logged-in
        // $app) rather than in a dedicated test, to avoid opening extra Postgres connections
        // against the shared, connection-limited test database
        $responseOverview = $app->handle($this->createRequest('/v2/event/test-event-slug/admin/programmes'));
        self::assertSame(200, $responseOverview->getStatusCode());
        $responseNewForm = $app->handle($this->createRequest('/v2/event/test-event-slug/admin/programmes/new'));
        self::assertSame(200, $responseNewForm->getStatusCode());

        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->getOneBy(['slug' => self::TEST_EVENT_SLUG]);
        $programme = $this->findByName($programmeRepository, $event, $uniqueName);

        self::assertNotNull($programme);
        self::assertSame($uniqueName, $programme->name);
        self::assertSame(15, $programme->capacity);
        self::assertFalse($programme->isPreregistered);
        self::assertSame($workshops->id, $programme->section->id);
        self::assertCount(2, $programme->getTargetRoles());

        $responseEdit = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes/' . $programme->id . '/edit',
            'POST',
            [
                'name' => $uniqueName . ' renamed',
                'description' => 'desc',
                'place' => 'tent B',
                'start' => '2027-06-05T10:00',
                'end' => '2027-06-05T12:00',
                'capacity' => '5',
                'sectionId' => (string)$games->id,
                'isPreregistered' => '1',
                'roles' => ['ist'],
            ],
        ));
        self::assertSame(302, $responseEdit->getStatusCode());

        $responseEditForm = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes/' . $programme->id . '/edit',
        ));
        self::assertSame(200, $responseEditForm->getStatusCode());

        $reloaded = $programmeRepository->getActiveById($programme->id, $event);
        self::assertSame($uniqueName . ' renamed', $reloaded->name);
        self::assertSame(5, $reloaded->capacity);
        self::assertTrue($reloaded->isPreregistered);
        self::assertSame($games->id, $reloaded->section->id);
        self::assertCount(1, $reloaded->getTargetRoles());

        $responseDelete = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes/' . $programme->id . '/delete',
            'POST',
        ));
        self::assertSame(302, $responseDelete->getStatusCode());

        $this->expectException(\RuntimeException::class);
        $programmeRepository->getActiveById($programme->id, $event);
    }

    public function testMovingProgrammeIntoExclusiveSectionWarnsAboutOverlappingRegistrations(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'programme-move-exclusive-admin@example.com');
        $event = $this->getEvent($app);
        $exclusive = $this->createProgrammeSection($app->getContainer(), $event, 'exclusive target', true);
        $open = $this->createProgrammeSection($app->getContainer(), $event, 'open source');
        $stayer = $this->createProgramme($app, 'stays in exclusive', $exclusive, 5);
        $mover = $this->createProgramme($app, 'moves into exclusive', $open, 5);
        $service = $this->getService($app, ProgrammeService::class);
        $doubleBooked = $this->createPaidIstWithEmail($app, 'double-booked-mover@example.com');
        self::assertTrue($service->adminAssign($stayer, $doubleBooked)->success);
        self::assertTrue($service->adminAssign($mover, $doubleBooked)->success);

        $response = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes/' . $mover->id . '/edit',
            'POST',
            [
                'name' => 'moves into exclusive',
                'description' => 'desc',
                'place' => 'tent A',
                'start' => '2027-06-05T10:00',
                'end' => '2027-06-05T12:00',
                'capacity' => '5',
                'sectionId' => (string)$exclusive->id,
                'roles' => ['ist'],
            ],
        ));

        self::assertSame(302, $response->getStatusCode());
        $flashed = $this->flashed($app);
        self::assertContains(
            str_replace('%count%', '1', $this->translate($app, 'flash.warning.programmeExclusiveConflicts')),
            $flashed,
        );
        self::assertContains($this->translate($app, 'flash.success.programmeSaved'), $flashed);
    }

    public function testFlippingProgrammeToPreregisteredSoftDeletesActiveRegistrations(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'preregistered-toggle-admin@example.com');
        $programme = $this->createProgramme($app, 'toggle to preregistered', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'toggle-preregistered-ist@example.com');

        $registrationRepository = $this->getService($app, ProgrammeRegistrationRepository::class);
        $registration = new ProgrammeRegistration();
        $registration->programme = $programme;
        $registration->participant = $ist;
        $registrationRepository->persist($registration);
        self::assertSame(1, $registrationRepository->countActiveForProgramme($programme));

        $responseEdit = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes/' . $programme->id . '/edit',
            'POST',
            [
                'name' => $programme->name,
                'description' => 'desc',
                'place' => 'tent A',
                'start' => '2027-06-05T10:00',
                'end' => '2027-06-05T12:00',
                'capacity' => '5',
                'sectionId' => (string)$programme->section->id,
                'isPreregistered' => '1',
                'roles' => ['ist'],
            ],
        ));
        self::assertSame(302, $responseEdit->getStatusCode());

        self::assertSame(0, $registrationRepository->countActiveForProgramme($programme));

        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->getOneBy(['slug' => self::TEST_EVENT_SLUG]);
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        $reloaded = $programmeRepository->getActiveById($programme->id, $event);
        self::assertTrue($reloaded->isPreregistered);
    }

    public function testTamperedRoleValuesAreSkippedNotFatal(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'tampered-role-admin@example.com');
        $section = $this->createProgrammeSection($app->getContainer(), $this->getEvent($app), 'tampered');

        $uniqueName = 'tampered role programme ' . bin2hex(random_bytes(4));
        $response = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes/new',
            'POST',
            [
                'name' => $uniqueName,
                'description' => 'desc',
                'place' => 'tent A',
                'start' => '2027-06-05T10:00',
                'end' => '2027-06-05T12:00',
                'capacity' => '15',
                'sectionId' => (string)$section->id,
                'roles' => ['ist', 'not-a-role'],
            ],
        ));
        self::assertSame(302, $response->getStatusCode());

        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->getOneBy(['slug' => self::TEST_EVENT_SLUG]);
        $programme = $this->findByName($programmeRepository, $event, $uniqueName);

        self::assertNotNull($programme);
        self::assertSame([ParticipantRole::Ist], $programme->getTargetRoles());
    }

    public function testAdminSetsProgrammeWindow(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'window-admin@example.com');

        $response = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes/window',
            'POST',
            [
                'programmeRegistrationStart' => '2027-05-01T12:00',
                'programmeRegistrationEnd' => '2027-05-20T12:00',
            ],
        ));
        self::assertSame(302, $response->getStatusCode());

        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->getOneBy(['slug' => self::TEST_EVENT_SLUG]);
        self::assertNotNull($event->programmeRegistrationStart);
        self::assertSame('2027-05-01 12:00', $event->programmeRegistrationStart->format('Y-m-d H:i'));
    }

    public function testProgrammeTextIsStoredUnescaped(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'raw-text-admin@example.com');

        $uniqueName = 'Kuchyně & "dílna" ' . bin2hex(random_bytes(4));
        $response = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes/new',
            'POST',
            $this->programmeBody($app, $uniqueName, '2027-06-05T10:00', '2027-06-05T12:00', [
                'description' => 'a < b',
                'place' => "O'Brien",
            ]),
        ));
        self::assertSame(302, $response->getStatusCode());

        $programmeRepository = $this->getService($app, ProgrammeRepository::class);

        $programme = $this->findByName($programmeRepository, $this->getEvent($app), $uniqueName);

        self::assertNotNull($programme);
        self::assertSame($uniqueName, $programme->name);
        self::assertSame('a < b', $programme->description);
        self::assertSame("O'Brien", $programme->place);
    }

    public function testProgrammeFormIsNotRenderedWhenEventHasNoSection(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'no-section-form-admin@example.com');
        $sectionsUrl = '/v2/event/test-event-slug/admin/programmes/sections';

        $bodyWithout = (string)$app->handle($this->createRequest('/v2/event/test-event-slug/admin/programmes/new'))
            ->getBody();
        self::assertStringContainsString($this->translate($app, 'programme.admin.formSectionNone'), $bodyWithout);
        self::assertStringContainsString('href="' . $sectionsUrl . '"', $bodyWithout);
        self::assertStringNotContainsString('<form', $bodyWithout);

        $this->createProgrammeSection($app->getContainer(), $this->getEvent($app), 'first section');
        $bodyWith = (string)$app->handle($this->createRequest('/v2/event/test-event-slug/admin/programmes/new'))
            ->getBody();
        self::assertStringContainsString('id="sectionId"', $bodyWith);
        self::assertStringNotContainsString($this->translate($app, 'programme.admin.formSectionNone'), $bodyWith);
    }

    public function testProgrammeFormRejectsSectionOfAnotherEvent(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'foreign-section-admin@example.com');
        $foreignEvent = $this->getSmallTestEvent($this->getService($app, EventRepository::class));
        $foreignSection = $this->createProgrammeSection($app->getContainer(), $foreignEvent, 'foreign');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Programme section not found for event');
        $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes/new',
            'POST',
            $this->programmeBody($app, 'foreign section', '2027-06-05T10:00', '2027-06-05T12:00', [
                'sectionId' => (string)$foreignSection->id,
            ]),
        ));
    }

    public function testProgrammeEditRejectsSectionOfAnotherEvent(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'foreign-section-edit-admin@example.com');
        $programme = $this->createProgramme($app, 'foreign section edit ' . bin2hex(random_bytes(4)), null, 5);
        $foreignEvent = $this->getSmallTestEvent($this->getService($app, EventRepository::class));
        $foreignSection = $this->createProgrammeSection($app->getContainer(), $foreignEvent, 'foreign');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Programme section not found for event');
        $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes/' . $programme->id . '/edit',
            'POST',
            $this->programmeBody($app, $programme->name, '2027-06-05T10:00', '2027-06-05T12:00', [
                'sectionId' => (string)$foreignSection->id,
            ]),
        ));
    }

    public function testCreateRejectsEndBeforeStart(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'end-before-start-admin@example.com');

        $uniqueName = 'end before start ' . bin2hex(random_bytes(4));
        $response = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes/new',
            'POST',
            $this->programmeBody($app, $uniqueName, '2027-06-05T12:00', '2027-06-05T10:00'),
        ));

        self::assertSame(302, $response->getStatusCode());
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        self::assertNull($this->findByName($programmeRepository, $this->getEvent($app), $uniqueName));
    }

    public function testCreateRejectsUnparsableStart(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'unparsable-start-admin@example.com');

        $uniqueName = 'unparsable start ' . bin2hex(random_bytes(4));
        $response = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes/new',
            'POST',
            $this->programmeBody($app, $uniqueName, 'abc', '2027-06-05T10:00'),
        ));

        self::assertSame(302, $response->getStatusCode());
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        self::assertNull($this->findByName($programmeRepository, $this->getEvent($app), $uniqueName));
    }

    public function testCreateRejectsOverflowingDate(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'overflowing-date-admin@example.com');

        $uniqueName = 'overflowing date ' . bin2hex(random_bytes(4));
        $response = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes/new',
            'POST',
            $this->programmeBody($app, $uniqueName, '2027-02-30T10:00', '2027-03-05T10:00'),
        ));

        self::assertSame(302, $response->getStatusCode());
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        self::assertNull($this->findByName($programmeRepository, $this->getEvent($app), $uniqueName));
    }

    public function testUpdateRejectsEndEqualToStart(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'end-equals-start-admin@example.com');
        $programme = $this->createProgramme($app, 'end equals start ' . bin2hex(random_bytes(4)), null, 5);
        $startBefore = $programme->start->format('Y-m-d H:i');
        $endBefore = $programme->end->format('Y-m-d H:i');

        $response = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes/' . $programme->id . '/edit',
            'POST',
            $this->programmeBody($app, $programme->name, '2027-06-05T15:00', '2027-06-05T15:00'),
        ));

        self::assertSame(302, $response->getStatusCode());
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        $reloaded = $programmeRepository->getActiveById($programme->id, $this->getEvent($app));
        self::assertSame($startBefore, $reloaded->start->format('Y-m-d H:i'));
        self::assertSame($endBefore, $reloaded->end->format('Y-m-d H:i'));
    }

    public function testWindowRejectsEndBeforeStart(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'window-end-before-start-admin@example.com');
        $before = $this->getEvent($app)->programmeRegistrationStart?->format('Y-m-d H:i');
        $endBefore = $this->getEvent($app)->programmeRegistrationEnd?->format('Y-m-d H:i');

        $response = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes/window',
            'POST',
            ['programmeRegistrationStart' => '2027-05-20T12:00', 'programmeRegistrationEnd' => '2027-05-01T12:00'],
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame($before, $this->getEvent($app)->programmeRegistrationStart?->format('Y-m-d H:i'));
        self::assertSame($endBefore, $this->getEvent($app)->programmeRegistrationEnd?->format('Y-m-d H:i'));
    }

    public function testWindowRejectsUnparsableValue(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'window-garbage-admin@example.com');
        $before = $this->getEvent($app)->programmeRegistrationStart?->format('Y-m-d H:i');
        $endBefore = $this->getEvent($app)->programmeRegistrationEnd?->format('Y-m-d H:i');

        $response = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes/window',
            'POST',
            ['programmeRegistrationStart' => 'garbage', 'programmeRegistrationEnd' => ''],
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame($before, $this->getEvent($app)->programmeRegistrationStart?->format('Y-m-d H:i'));
        self::assertSame($endBefore, $this->getEvent($app)->programmeRegistrationEnd?->format('Y-m-d H:i'));
    }

    public function testWindowAllowsOneSideOnly(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'window-one-side-admin@example.com');

        $response = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes/window',
            'POST',
            ['programmeRegistrationStart' => '2027-05-01T12:00', 'programmeRegistrationEnd' => ''],
        ));

        self::assertSame(302, $response->getStatusCode());
        $event = $this->getEvent($app);
        self::assertSame('2027-05-01 12:00', $event->programmeRegistrationStart?->format('Y-m-d H:i'));
        self::assertNull($event->programmeRegistrationEnd);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function getEvent(App $app): Event
    {
        return $this->getService($app, EventRepository::class)->getOneBy(['slug' => self::TEST_EVENT_SLUG]);
    }

    /**
     * @param App<ContainerInterface> $app
     * @param array<string, list<string>|string> $overrides
     * @return array<string, list<string>|string>
     */
    private function programmeBody(App $app, string $name, string $start, string $end, array $overrides = []): array
    {
        $section = $this->createProgrammeSection($app->getContainer(), $this->getEvent($app), 'form section');

        return array_merge([
            'name' => $name,
            'description' => 'desc',
            'place' => 'tent A',
            'start' => $start,
            'end' => $end,
            'capacity' => '5',
            'sectionId' => (string)$section->id,
            'roles' => ['ist'],
        ], $overrides);
    }

    public function testAdminAssignsAndRemovesParticipant(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'assign-admin@example.com');
        $programme = $this->createProgramme($app, 'assignable', null, 1);
        $ist = $this->createPaidIstWithEmail($app, 'assignee@example.com');

        $responseAssign = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes/' . $programme->id . '/assign',
            'POST',
            ['participantId' => (string)$ist->id],
        ));
        self::assertSame(302, $responseAssign->getStatusCode());

        $registrationRepository = $this->getService($app, ProgrammeRegistrationRepository::class);
        self::assertSame(1, $registrationRepository->countActiveForProgramme($programme));

        $responseDetail = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes/' . $programme->id,
        ));
        self::assertSame(200, $responseDetail->getStatusCode());

        $responseRemove = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes/' . $programme->id . '/remove/' . $ist->id,
            'POST',
        ));
        self::assertSame(302, $responseRemove->getStatusCode());
        self::assertSame(0, $registrationRepository->countActiveForProgramme($programme));
    }

    public function testAssignToPreregisteredProgrammeIsGracefullyRejected(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'preregistered-assign-admin@example.com');
        $programme = $this->createProgramme($app, 'ceremony', null, 999, true);
        $ist = $this->createPaidIstWithEmail($app, 'preregistered-assignee@example.com');

        $responseAssign = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes/' . $programme->id . '/assign',
            'POST',
            ['participantId' => (string)$ist->id],
        ));
        self::assertSame(302, $responseAssign->getStatusCode());

        $registrationRepository = $this->getService($app, ProgrammeRegistrationRepository::class);
        self::assertSame(0, $registrationRepository->countActiveForProgramme($programme));
    }

    public function testAssignCancelledParticipantIsGracefullyRejected(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'cancelled-assign-admin@example.com');
        $programme = $this->createProgramme($app, 'cancelled-assign workshop', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'cancelled-assignee@example.com');
        $userRepository = $this->getService($app, UserRepository::class);
        $user = $ist->getUserButNotNull();
        $user->status = UserStatus::Cancelled;
        $userRepository->persist($user);

        $responseAssign = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes/' . $programme->id . '/assign',
            'POST',
            ['participantId' => (string)$ist->id],
        ));
        self::assertSame(302, $responseAssign->getStatusCode());

        $registrationRepository = $this->getService($app, ProgrammeRegistrationRepository::class);
        self::assertSame(0, $registrationRepository->countActiveForProgramme($programme));
    }

    public function testAssignUnknownParticipantIdIsGracefullyRejected(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'unknown-id-assign-admin@example.com');
        $programme = $this->createProgramme($app, 'unknown id workshop', null, 5);

        $responseAssign = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes/' . $programme->id . '/assign',
            'POST',
            ['participantId' => '999999'],
        ));
        self::assertSame(302, $responseAssign->getStatusCode());

        $registrationRepository = $this->getService($app, ProgrammeRegistrationRepository::class);
        self::assertSame(0, $registrationRepository->countActiveForProgramme($programme));
    }

    public function testAssignParticipantOfOtherEventIsRejected(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'other-event-assign-admin@example.com');
        $programme = $this->createProgramme($app, 'other event workshop', null, 5);
        $otherEvent = $this->getSmallTestEvent($this->getService($app, EventRepository::class));
        $foreigner = $this->createParticipantWithRole($app->getContainer(), 'ist', $otherEvent);

        $responseAssign = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes/' . $programme->id . '/assign',
            'POST',
            ['participantId' => (string)$foreigner->id],
        ));
        self::assertSame(302, $responseAssign->getStatusCode());

        $registrationRepository = $this->getService($app, ProgrammeRegistrationRepository::class);
        self::assertSame(0, $registrationRepository->countActiveForProgramme($programme));
    }

    public function testDetailRendersParticipantIdInputInsteadOfSelect(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'id-input-admin@example.com');
        $programme = $this->createProgramme($app, 'id input workshop', null, 5);

        $response = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes/' . $programme->id,
        ));
        $body = (string)$response->getBody();

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('type="number"', $body);
        self::assertStringContainsString('name="participantId"', $body);
        self::assertStringNotContainsString('<select', $body);
    }

    public function testProgrammeExportRoutesAreServedToAdmin(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'export-routes-admin@example.com');
        $programme = $this->createProgramme($app, 'export route workshop', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'export-route-ist@example.com');
        $this->getService($app, ProgrammeService::class)->adminAssign($programme, $ist);

        $csvResponse = $app->handle($this->createRequest('/v2/event/test-event-slug/admin/export/programmes'));
        $csvBody = (string)$csvResponse->getBody();
        self::assertSame(200, $csvResponse->getStatusCode());
        self::assertStringContainsString('text/csv', $csvResponse->getHeaderLine('Content-Type'));
        self::assertStringContainsString('export route workshop', $csvBody);
        self::assertStringContainsString('export-route-ist@example.com', $csvBody);

        $printAllResponse = $app->handle(
            $this->createRequest('/v2/event/test-event-slug/admin/export/programmes-print'),
        );
        self::assertSame(200, $printAllResponse->getStatusCode());
        self::assertStringContainsString('application/pdf', $printAllResponse->getHeaderLine('Content-Type'));

        $printOneResponse = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes/' . $programme->id . '/print',
        ));
        self::assertSame(200, $printOneResponse->getStatusCode());
        self::assertStringContainsString('application/pdf', $printOneResponse->getHeaderLine('Content-Type'));
    }

    public function testAdminImageRouteServesImageWhileWindowIsClosed(): void
    {
        $app = $this->getTestApp();
        $this->closeProgrammeWindow($app);
        $app = $this->createLoggedAdmin($app, 'image-route-admin@example.com');
        $programmeWithImage = $this->createProgramme($app, 'admin image workshop', null, 5);
        $uploadPath = $this->attachImage($app, $programmeWithImage);
        $programmeWithoutImage = $this->createProgramme($app, 'admin imageless workshop', null, 5);

        try {
            $response = $app->handle($this->createRequest(
                '/v2/event/test-event-slug/admin/programmes/' . $programmeWithImage->id . '/image',
            ));
            self::assertSame(200, $response->getStatusCode());
            self::assertSame('nosniff', $response->getHeaderLine('X-Content-Type-Options'));
            self::assertStringStartsWith("\x89PNG", (string)$response->getBody());

            $responseWithoutImage = $app->handle($this->createRequest(
                '/v2/event/test-event-slug/admin/programmes/' . $programmeWithoutImage->id . '/image',
            ));
            self::assertSame(404, $responseWithoutImage->getStatusCode());
        } finally {
            @unlink($uploadPath);
        }
    }

    public function testOverviewShowsProgrammesAsCardsGroupedByDay(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'overview-cards-admin@example.com');
        $programmeWithImage = $this->createProgramme($app, 'overview card with image', null, 5);
        $uploadPath = $this->attachImage($app, $programmeWithImage);
        $programmeNextDay = $this->createProgramme($app, 'overview card next day', null, 5);
        $programmeNextDay->start = DateTimeUtils::getDateTime('2027-06-06 09:00');
        $programmeNextDay->end = DateTimeUtils::getDateTime('2027-06-06 11:00');
        $this->getService($app, ProgrammeRepository::class)->persist($programmeNextDay);

        try {
            $response = $app->handle($this->createRequest('/v2/event/test-event-slug/admin/programmes'));
        } finally {
            @unlink($uploadPath);
        }
        $body = (string)$response->getBody();

        self::assertSame(200, $response->getStatusCode());
        self::assertStringNotContainsString('<table', $body);
        self::assertStringContainsString('href="/v2/event/test-event-slug/admin/dashboard"', $body);
        self::assertStringContainsString('<h2>5. 6. 2027</h2>', $body);
        self::assertStringContainsString('<h2>6. 6. 2027</h2>', $body);

        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->getOneBy(['slug' => self::TEST_EVENT_SLUG]);
        $programmes = $this->getService($app, ProgrammeRepository::class)->findActiveForEvent($event);
        $days = [];
        foreach ($programmes as $programme) {
            $days[$programme->start->format('Y-m-d')] = true;
            self::assertStringContainsString(
                '<h3><a href="/v2/event/test-event-slug/admin/programmes/' . $programme->id . '">',
                $body,
            );
        }
        self::assertSame(1 + count($days) + count($programmes), substr_count($body, 'class="card"'));

        $imagePath = '/v2/event/test-event-slug/admin/programmes/%d/image';
        self::assertStringContainsString(sprintf($imagePath, $programmeWithImage->id), $body);
        self::assertStringNotContainsString(sprintf($imagePath, $programmeNextDay->id), $body);
    }

    public function testDetailShowsRegistrantsInCardWithMendLinkForAdmin(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'detail-card-admin@example.com');
        $programme = $this->createProgramme($app, 'detail card workshop', null, 5);
        $ist = $this->createNamedRegistrant($app, $programme, 'detail-card-ist@example.com');
        $uploadPath = $this->attachImage($app, $programme);

        try {
            $response = $app->handle($this->createRequest(
                '/v2/event/test-event-slug/admin/programmes/' . $programme->id,
            ));
        } finally {
            @unlink($uploadPath);
        }
        $body = (string)$response->getBody();

        self::assertSame(200, $response->getStatusCode());
        self::assertStringNotContainsString('<table', $body);
        self::assertStringContainsString(
            '<a href="/v2/event/test-event-slug/admin/' . $ist->id . '/mend">Detailo Registrant</a>',
            $body,
        );
        self::assertStringContainsString(
            'action="/v2/event/test-event-slug/admin/programmes/' . $programme->id . '/remove/' . $ist->id . '"',
            $body,
        );
        self::assertStringContainsString(
            'action="/v2/event/test-event-slug/admin/programmes/' . $programme->id . '/assign"',
            $body,
        );
        self::assertStringContainsString(
            'src="/v2/event/test-event-slug/admin/programmes/' . $programme->id . '/image"',
            $body,
        );
    }

    public function testEditFormRendersAsCardWithCurrentImage(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'edit-card-admin@example.com');
        $programme = $this->createProgramme($app, 'edit card workshop', null, 5);
        $uploadPath = $this->attachImage($app, $programme);

        try {
            $response = $app->handle($this->createRequest(
                '/v2/event/test-event-slug/admin/programmes/' . $programme->id . '/edit',
            ));
        } finally {
            @unlink($uploadPath);
        }
        $body = (string)$response->getBody();

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('card text-left', $body);
        self::assertStringContainsString('class="programme-thumbnail"', $body);
        self::assertStringContainsString(
            'src="/v2/event/test-event-slug/admin/programmes/' . $programme->id . '/image"',
            $body,
        );
        self::assertStringContainsString('form-control form-wide', $body);
        self::assertStringContainsString('class="btn btn-small" type="submit"', $body);
        self::assertStringContainsString('btn btn-small btn-grey', $body);
        self::assertStringContainsString('name="image"', $body);
    }

    public function testNewFormRendersWithoutThumbnail(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'new-card-admin@example.com');
        $this->createProgrammeSection($app->getContainer(), $this->getEvent($app), 'new card section');

        $response = $app->handle($this->createRequest('/v2/event/test-event-slug/admin/programmes/new'));
        $body = (string)$response->getBody();

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('card text-left', $body);
        self::assertStringContainsString('form-control form-wide', $body);
        self::assertStringNotContainsString('programme-thumbnail', $body);
    }

    public function testDetailHidesMendLinkFromProgrammeAdmin(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'detail-card-programme-admin@example.com', UserRole::ProgrammeAdmin);
        $programme = $this->createProgramme($app, 'detail card scoped workshop', null, 5);
        $ist = $this->createNamedRegistrant($app, $programme, 'detail-card-scoped-ist@example.com');

        $response = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes/' . $programme->id,
        ));
        $body = (string)$response->getBody();

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('Detailo Registrant', $body);
        self::assertStringNotContainsString('/admin/' . $ist->id . '/mend', $body);
        self::assertStringContainsString(
            'action="/v2/event/test-event-slug/admin/programmes/' . $programme->id . '/remove/' . $ist->id . '"',
            $body,
        );
    }

    public function testOverviewHidesDashboardLinkFromProgrammeAdmin(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'overview-programme-admin@example.com', UserRole::ProgrammeAdmin);

        $response = $app->handle($this->createRequest('/v2/event/test-event-slug/admin/programmes'));
        $body = (string)$response->getBody();

        self::assertSame(200, $response->getStatusCode());
        self::assertStringNotContainsString('href="/v2/event/test-event-slug/admin/dashboard"', $body);
    }

    public function testDetailOfPreregisteredProgrammeShowsNoteInsteadOfAssignForm(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'detail-card-preregistered-admin@example.com');
        $programme = $this->createProgramme($app, 'detail card ceremony', null, 999, true);

        $response = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes/' . $programme->id,
        ));
        $body = (string)$response->getBody();
        $note = $this->getService($app, TranslatorInterface::class)->trans('programme.admin.preregisteredNote');

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString($note, $body);
        self::assertStringNotContainsString('/programmes/' . $programme->id . '/assign', $body);
    }

    public function testOverviewMarksOnlyOverCapacityProgramme(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'overview-capacity-admin@example.com');
        $overCapacity = $this->createProgramme($app, 'overview over capacity', null, 1);
        $withinCapacity = $this->createProgramme($app, 'overview within capacity', null, 5);
        $registrationRepository = $this->getService($app, ProgrammeRegistrationRepository::class);
        $emails = [
            'capacity-first-ist@example.com' => $overCapacity,
            'capacity-second-ist@example.com' => $overCapacity,
            'capacity-third-ist@example.com' => $withinCapacity,
        ];
        foreach ($emails as $email => $programme) {
            $registration = new ProgrammeRegistration();
            $registration->programme = $programme;
            $registration->participant = $this->createPaidIstWithEmail($app, $email);
            $registrationRepository->persist($registration);
        }

        $response = $app->handle($this->createRequest('/v2/event/test-event-slug/admin/programmes'));
        $body = (string)$response->getBody();

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('2/1 ⚠', $this->extractOverviewCard($body, $overCapacity));
        self::assertStringNotContainsString('⚠', $this->extractOverviewCard($body, $withinCapacity));
        self::assertStringContainsString('1/5', $this->extractOverviewCard($body, $withinCapacity));
    }

    public function testOverviewCardShowsTranslatedTargetRoles(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'overview-roles-admin@example.com');
        $programme = $this->createProgramme($app, 'overview roles workshop', null, 5);
        $programme->setTargetRoles([ParticipantRole::Ist, ParticipantRole::TroopLeader]);
        $this->getService($app, ProgrammeRepository::class)->persist($programme);

        $response = $app->handle($this->createRequest('/v2/event/test-event-slug/admin/programmes'));
        $card = $this->extractOverviewCard((string)$response->getBody(), $programme);
        $translator = $this->getService($app, TranslatorInterface::class);

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString(htmlspecialchars($translator->trans('role.ist')), $card);
        self::assertStringContainsString(htmlspecialchars($translator->trans('role.tl')), $card);
        self::assertStringNotContainsString('role.', $card);
    }

    public function testProgrammeAdminReachesAdminImageRoute(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'image-route-programme-admin@example.com', UserRole::ProgrammeAdmin);
        $programme = $this->createProgramme($app, 'programme admin image workshop', null, 5);
        $uploadPath = $this->attachImage($app, $programme);

        try {
            $response = $app->handle($this->createRequest(
                '/v2/event/test-event-slug/admin/programmes/' . $programme->id . '/image',
            ));
        } finally {
            @unlink($uploadPath);
        }

        self::assertSame(200, $response->getStatusCode());
    }

    public function testAdminImageServesProgrammeOfAnyRole(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'image-any-role-programme-admin@example.com', UserRole::ProgrammeAdmin);
        $programme = $this->createProgramme($app, 'patrol leaders image workshop', null, 5);
        $programme->setTargetRoles([ParticipantRole::PatrolLeader]);
        $uploadPath = $this->attachImage($app, $programme);

        try {
            $response = $app->handle($this->createRequest(
                '/v2/event/test-event-slug/admin/programmes/' . $programme->id . '/image',
            ));
        } finally {
            @unlink($uploadPath);
        }

        self::assertSame(200, $response->getStatusCode());
    }

    public function testNonProgrammeAdminIsDenied(): void
    {
        $app = $this->getTestApp();
        $user = $this->registerUser($app, 'plain-user@example.com');
        $app = $this->loginAs($app, $user);

        $response = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes',
        ));
        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/v2/event/test-event-slug/getDashboard', $response->getHeaderLine('Location'));
    }

    public function testIstAdminDeniedLandsOnRoleDashboard(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedAdmin($app, 'ist-admin-denied@example.com', UserRole::IstAdmin);

        $response = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes',
        ));
        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/v2/event/test-event-slug/getDashboard', $response->getHeaderLine('Location'));
    }

    private function findByName(ProgrammeRepository $programmeRepository, Event $event, string $name): ?Programme
    {
        foreach ($programmeRepository->findActiveForEvent($event) as $candidate) {
            if ($candidate->name === $name) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createProgramme(
        App $app,
        string $name,
        ?ProgrammeSection $section,
        int $capacity,
        bool $isPreregistered = false,
    ): Programme {
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);

        $programme = new Programme();
        $programme->name = $name;
        $programme->description = 'description';
        $programme->place = 'somewhere';
        $programme->imageFilename = null;
        $programme->start = DateTimeUtils::getDateTime('2027-06-05 10:00');
        $programme->end = DateTimeUtils::getDateTime('2027-06-05 12:00');
        $programme->capacity = $capacity;
        $programme->isPreregistered = $isPreregistered;
        $programme->event = $eventRepository->getOneBy(['slug' => self::TEST_EVENT_SLUG]);
        $programme->section = $section
            ?? $this->createProgrammeSection($app->getContainer(), $programme->event, 'Program');
        $programme->setTargetRoles([ParticipantRole::Ist]);
        $programmeRepository->persist($programme);

        return $programme;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createNamedRegistrant(App $app, Programme $programme, string $email): Participant
    {
        $ist = $this->createPaidIstWithEmail($app, $email);
        $ist->firstName = 'Detailo';
        $ist->lastName = 'Registrant';
        $this->getService($app, ParticipantRepository::class)->persist($ist);
        $this->getService($app, ProgrammeService::class)->adminAssign($programme, $ist);

        return $ist;
    }

    private function extractOverviewCard(string $body, Programme $programme): string
    {
        $cardStart = strpos($body, '/admin/programmes/' . $programme->id . '">');
        self::assertNotFalse($cardStart, 'card of programme ' . $programme->id . ' not found');
        $cardEnd = strpos($body, 'class="card"', $cardStart);

        return substr($body, $cardStart, $cardEnd === false ? null : $cardEnd - $cardStart);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function attachImage(App $app, Programme $programme): string
    {
        $storedFilename = bin2hex(random_bytes(16));
        $programme->imageFilename = $storedFilename;
        $this->getService($app, ProgrammeRepository::class)->persist($programme);

        $uploadPath = __DIR__ . '/../../uploads/' . $storedFilename;
        $image = imagecreatetruecolor(1, 1);
        imagepng($image, $uploadPath);
        imagedestroy($image);

        return $uploadPath;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function closeProgrammeWindow(App $app): void
    {
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->getOneBy(['slug' => self::TEST_EVENT_SLUG]);
        $event->programmeRegistrationStart = null;
        $event->programmeRegistrationEnd = null;
        $eventRepository->persist($event);
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
     * @return App<ContainerInterface>
     */
    private function createLoggedAdmin(App $app, string $email, UserRole $role = UserRole::Admin): App
    {
        $user = $this->registerUser($app, $email);
        $userRepository = $this->getService($app, UserRepository::class);
        $user->role = $role;
        $userRepository->persist($user);

        return $this->loginAs($app, $user);
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

    /**
     * @param App<ContainerInterface> $app
     * @return App<ContainerInterface>
     */
    private function loginAs(App $app, User $user): App
    {
        $loginTokenRepository = $this->getService($app, LoginTokenRepository::class);

        $loginToken = new LoginToken();
        $loginToken->token = bin2hex(random_bytes(16));
        $loginToken->user = $user;
        $loginToken->used = false;
        $loginTokenRepository->persist($loginToken);

        $response = $app->handle($this->createRequest(
            '/v2/event/' . self::TEST_EVENT_SLUG . '/tryLogin/' . $loginToken->token,
        ));
        self::assertSame(302, $response->getStatusCode());

        // the container (and the UserRegeneration singleton reading $_SESSION at construction
        // time) is scoped to one $app instance, so a fresh one is needed to pick up the login
        return $this->getTestApp(false);
    }
}
