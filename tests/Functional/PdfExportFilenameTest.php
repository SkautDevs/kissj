<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\EventRepository;
use kissj\Participant\ParticipantRole;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeRepository;
use kissj\User\UserRole;
use Psr\Http\Message\ResponseInterface;
use Tests\AppTestCase;

class PdfExportFilenameTest extends AppTestCase
{
    public function testExportBadgesSetsInlineFilenameWithSlugRolesAndTimestamp(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->get(1);
        $event->logoUrl = '/favicon-16x16.png';
        $eventRepository->persist($event);
        $app = $this->loginAdminForEvent($app, $event, UserRole::Admin);

        $request = $this->createRequest(
            '/v2/event/' . $event->slug . '/admin/export/badges/generate-full',
        )->withQueryParams(['roles' => ['pl', 'ist']]);
        $response = $app->handle($request);

        $this->assertInlinePdfFilename($response, $event->slug . '_badges_pl-ist');
    }

    public function testExportBlankBadgesSetsInlineFilenameWithSlugAndTimestamp(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->get(1);
        $event->logoUrl = '/favicon-16x16.png';
        $eventRepository->persist($event);
        $app = $this->loginAdminForEvent($app, $event, UserRole::Admin);

        $request = $this->createRequest(
            '/v2/event/' . $event->slug . '/admin/export/badges/generate-blank',
        )->withQueryParams(['count' => '4']);
        $response = $app->handle($request);

        $this->assertInlinePdfFilename($response, $event->slug . '_badges_blank');
    }

    public function testExportPatrolsRosterSetsInlineFilename(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->get(1);
        $app = $this->loginAdminForEvent($app, $event, UserRole::Admin);

        $request = $this->createRequest(
            '/v2/event/' . $event->slug . '/admin/export/patrolsRoster',
        );
        $response = $app->handle($request);

        $this->assertInlinePdfFilename($response, $event->slug . '_patrols_roster');
    }

    public function testExportProgrammesPrintSetsInlineFilename(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->get(1);
        $app = $this->loginAdminForEvent($app, $event, UserRole::Admin);

        $request = $this->createRequest(
            '/v2/event/' . $event->slug . '/admin/export/programmes-print',
        );
        $response = $app->handle($request);

        $this->assertInlinePdfFilename($response, $event->slug . '_programmes');
    }

    public function testPrintProgrammeSetsInlineFilename(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        $event = $eventRepository->get(1);

        $programme = new Programme();
        $programme->event = $event;
        $programme->name = 'PDF filename test';
        $programme->description = '';
        $programme->place = 'Hall';
        $programme->start = DateTimeUtils::getDateTime('2027-06-05 10:00');
        $programme->end = DateTimeUtils::getDateTime('2027-06-05 11:00');
        $programme->capacity = 10;
        $programme->isPreregistered = false;
        $programme->section = $this->createProgrammeSection($app->getContainer(), $event, 'Program');
        $programme->setTargetRoles([ParticipantRole::Ist]);
        $programmeRepository->persist($programme);

        $app = $this->loginAdminForEvent($app, $event, UserRole::Admin);

        $request = $this->createRequest(
            '/v2/event/' . $event->slug . '/admin/programmes/' . $programme->id . '/print',
        );
        $response = $app->handle($request);

        $this->assertInlinePdfFilename($response, $event->slug . '_programme_' . $programme->id);
    }

    private function assertInlinePdfFilename(ResponseInterface $response, string $expectedPrefix): void
    {
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/pdf', $response->getHeaderLine('Content-Type'));
        self::assertMatchesRegularExpression(
            '/^inline; filename="' . preg_quote($expectedPrefix, '/') . '_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.pdf"$/',
            $response->getHeaderLine('Content-Disposition'),
        );
    }
}
