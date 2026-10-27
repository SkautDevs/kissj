<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRole;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeRegistration;
use kissj\Programme\ProgrammeRegistrationRepository;
use kissj\Programme\ProgrammeRepository;
use kissj\Programme\ProgrammeSection;
use kissj\Programme\ProgrammeSectionRepository;
use kissj\User\LoginToken;
use kissj\User\LoginTokenRepository;
use kissj\User\UserRepository;
use kissj\User\UserRole;
use kissj\User\UserService;
use kissj\User\UserStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Slim\App;
use Tests\AppTestCase;

class ProgrammeSectionAdminTest extends AppTestCase
{
    private const string SECTIONS_URL = '/v2/event/test-event-slug/admin/programmes/sections';

    public function testCreatesEditsAndListsSections(): void
    {
        $app = $this->createLoggedAdmin('section-admin-create@example.com');
        $suffix = bin2hex(random_bytes(4));
        $firstName = 'first ' . $suffix;
        $secondName = 'second ' . $suffix;

        $responseFirst = $app->handle($this->createRequest(
            self::SECTIONS_URL,
            'POST',
            ['name' => $firstName, 'subtitle' => '', 'isExclusive' => '1'],
        ));
        self::assertSame(302, $responseFirst->getStatusCode());
        self::assertContains($this->translate($app, 'flash.success.programmeSectionSaved'), $this->flashed($app));
        $app->handle($this->createRequest(
            self::SECTIONS_URL,
            'POST',
            ['name' => $secondName, 'subtitle' => 'sub ' . $suffix],
        ));

        $first = $this->findSectionByName($app, $firstName);
        $second = $this->findSectionByName($app, $secondName);
        self::assertNotNull($first);
        self::assertNotNull($second);
        self::assertNull($first->subtitle);
        self::assertTrue($first->isExclusive);
        self::assertSame('sub ' . $suffix, $second->subtitle);
        self::assertFalse($second->isExclusive);

        $responseList = $app->handle($this->createRequest(self::SECTIONS_URL));
        self::assertSame(200, $responseList->getStatusCode());
        $body = (string)$responseList->getBody();
        self::assertStringContainsString($firstName, $body);
        self::assertStringContainsString($secondName, $body);
        self::assertStringContainsString('sub ' . $suffix, $body);

        $responseEdit = $app->handle($this->createRequest(
            self::SECTIONS_URL . '/' . $second->id . '/edit',
            'POST',
            ['name' => $secondName . ' renamed', 'subtitle' => '', 'isExclusive' => '1'],
        ));
        self::assertSame(302, $responseEdit->getStatusCode());
        $reloaded = $this->findSectionByName($app, $secondName . ' renamed');
        self::assertNotNull($reloaded);
        self::assertSame($second->id, $reloaded->id);
        self::assertTrue($reloaded->isExclusive);
        self::assertNull($reloaded->subtitle);
    }

    public function testEnablingExclusivityWarnsAboutOverlappingRegistrations(): void
    {
        $app = $this->createLoggedAdmin('section-admin-exclusive-conflict@example.com');
        $container = $app->getContainer();
        $event = $this->getEvent($app);
        $section = $this->createProgrammeSection($container, $event, 'becomes exclusive');
        $first = $this->createProgramme($app, $event, $section, false);
        $second = $this->createProgramme($app, $event, $section, false);
        $deleted = $this->createProgramme($app, $event, $section, true);
        $afternoon = $this->createProgramme($app, $event, $section, false);
        $afternoon->start = DateTimeUtils::getDateTime('2027-06-05 12:00');
        $afternoon->end = DateTimeUtils::getDateTime('2027-06-05 14:00');
        $this->getService($app, ProgrammeRepository::class)->persist($afternoon);

        $doubleBooked = $this->createParticipantWithRole($container, 'ist', $event);
        $singleBooked = $this->createParticipantWithRole($container, 'ist', $event);
        $withDeleted = $this->createParticipantWithRole($container, 'ist', $event);
        $withCancelledRegistration = $this->createParticipantWithRole($container, 'ist', $event);
        $cancelled = $this->createParticipantWithRole($container, 'ist', $event);
        $this->register($app, $first, $doubleBooked);
        $this->register($app, $second, $doubleBooked);
        $this->register($app, $first, $singleBooked);
        $this->register($app, $afternoon, $singleBooked);
        $this->register($app, $first, $withDeleted);
        $this->register($app, $deleted, $withDeleted);
        $this->register($app, $first, $withCancelledRegistration);
        $this->getService($app, ProgrammeRegistrationRepository::class)
            ->softDelete($this->register($app, $second, $withCancelledRegistration));
        $this->register($app, $first, $cancelled);
        $this->register($app, $second, $cancelled);
        $cancelledUser = $cancelled->getUserButNotNull();
        $cancelledUser->status = UserStatus::Cancelled;
        $this->getService($app, UserRepository::class)->persist($cancelledUser);

        $response = $app->handle($this->createRequest(
            self::SECTIONS_URL . '/' . $section->id . '/edit',
            'POST',
            ['name' => 'becomes exclusive', 'subtitle' => '', 'isExclusive' => '1'],
        ));

        self::assertSame(302, $response->getStatusCode());
        $flashed = $this->flashed($app);
        self::assertContains($this->translate($app, 'flash.success.programmeSectionSaved'), $flashed);
        self::assertContains(
            str_replace('%count%', '1', $this->translate($app, 'flash.warning.programmeSectionExclusiveConflicts')),
            $flashed,
        );
        self::assertTrue($this->reloadSection($app, $section)->isExclusive);
    }

    public function testEnablingExclusivityWithoutOverlapsDoesNotWarn(): void
    {
        $app = $this->createLoggedAdmin('section-admin-exclusive-clean@example.com');
        $container = $app->getContainer();
        $event = $this->getEvent($app);
        $section = $this->createProgrammeSection($container, $event, 'clean exclusive');
        $programme = $this->createProgramme($app, $event, $section, false);
        $this->register($app, $programme, $this->createParticipantWithRole($container, 'ist', $event));

        $app->handle($this->createRequest(
            self::SECTIONS_URL . '/' . $section->id . '/edit',
            'POST',
            ['name' => 'clean exclusive', 'subtitle' => '', 'isExclusive' => '1'],
        ));

        self::assertSame([$this->translate($app, 'flash.success.programmeSectionSaved')], $this->flashed($app));
    }

    public function testDeleteRefusedWhileProgrammesAssigned(): void
    {
        $app = $this->createLoggedAdmin('section-admin-refuse@example.com');
        $event = $this->getEvent($app);
        $section = $this->createProgrammeSection($app->getContainer(), $event, 'with deleted programme');
        $this->createProgramme($app, $event, $section, true);

        $response = $app->handle($this->createRequest(
            self::SECTIONS_URL . '/' . $section->id . '/delete',
            'POST',
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertContains(
            str_replace('%count%', '1', $this->translate($app, 'flash.error.programmeSectionHasProgrammes')),
            $this->flashed($app),
        );
        self::assertNotNull($this->getService($app, ProgrammeSectionRepository::class)
            ->findOneBy(['id' => $section->id]));
    }

    public function testOverviewCountsOnlyActiveProgrammesButDeleteCountsAll(): void
    {
        $app = $this->createLoggedAdmin('section-admin-count@example.com');
        $event = $this->getEvent($app);
        $name = 'counted section ' . bin2hex(random_bytes(4));
        $section = $this->createProgrammeSection($app->getContainer(), $event, $name);
        $this->createProgramme($app, $event, $section, false);
        $this->createProgramme($app, $event, $section, true);
        $this->createProgramme($app, $event, $section, true);
        $deletedLabel = str_replace('%count%', '2', $this->translate($app, 'programme.admin.sections.deletedCount'));

        $body = (string)$app->handle($this->createRequest(self::SECTIONS_URL))->getBody();
        self::assertSame(1, preg_match('~value="' . $name . '".*?<b>(\d+)</b>([^<]*)<~s', $body, $matches));
        self::assertSame('1', $matches[1]);
        self::assertSame($deletedLabel, trim($matches[2]));

        $app->handle($this->createRequest(self::SECTIONS_URL . '/' . $section->id . '/delete', 'POST'));
        self::assertContains(
            str_replace('%count%', '3', $this->translate($app, 'flash.error.programmeSectionHasProgrammes')),
            $this->flashed($app),
        );
        self::assertNotNull($this->getService($app, ProgrammeSectionRepository::class)
            ->findOneBy(['id' => $section->id]));
    }

    public function testOverviewOmitsDeletedCountWithoutDeletedProgrammes(): void
    {
        $app = $this->createLoggedAdmin('section-admin-count-active@example.com');
        $event = $this->getEvent($app);
        $name = 'active only section ' . bin2hex(random_bytes(4));
        $section = $this->createProgrammeSection($app->getContainer(), $event, $name);
        $this->createProgramme($app, $event, $section, false);

        $body = (string)$app->handle($this->createRequest(self::SECTIONS_URL))->getBody();
        self::assertSame(1, preg_match('~value="' . $name . '".*?<b>(\d+)</b>([^<]*)<~s', $body, $matches));
        self::assertSame('1', $matches[1]);
        self::assertSame('', trim($matches[2]));
    }

    public function testBlankNameIsRefusedOnCreateAndEdit(): void
    {
        $app = $this->createLoggedAdmin('section-admin-blank-name@example.com');
        $event = $this->getEvent($app);
        $section = $this->createProgrammeSection($app->getContainer(), $event, 'keeps its name');
        $sectionCountBefore = count($this->getService($app, ProgrammeSectionRepository::class)->findForEvent($event));

        $responseCreate = $app->handle($this->createRequest(
            self::SECTIONS_URL,
            'POST',
            ['name' => '   ', 'subtitle' => ''],
        ));
        $responseEdit = $app->handle($this->createRequest(
            self::SECTIONS_URL . '/' . $section->id . '/edit',
            'POST',
            ['name' => '', 'subtitle' => 'new subtitle', 'isExclusive' => '1'],
        ));

        self::assertSame(302, $responseCreate->getStatusCode());
        self::assertSame(302, $responseEdit->getStatusCode());
        self::assertSame(
            array_fill(0, 2, $this->translate($app, 'flash.error.programmeSectionNameRequired')),
            $this->flashed($app),
        );
        self::assertCount(
            $sectionCountBefore,
            $this->getService($app, ProgrammeSectionRepository::class)->findForEvent($event),
        );
        $reloaded = $this->reloadSection($app, $section);
        self::assertSame('keeps its name', $reloaded->name);
        self::assertNull($reloaded->subtitle);
        self::assertFalse($reloaded->isExclusive);
    }

    public function testDeletesEmptySection(): void
    {
        $app = $this->createLoggedAdmin('section-admin-delete@example.com');
        $event = $this->getEvent($app);
        $section = $this->createProgrammeSection($app->getContainer(), $event, 'to delete');

        $response = $app->handle($this->createRequest(
            self::SECTIONS_URL . '/' . $section->id . '/delete',
            'POST',
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertContains($this->translate($app, 'flash.success.programmeSectionDeleted'), $this->flashed($app));
        self::assertNull($this->getService($app, ProgrammeSectionRepository::class)
            ->findOneBy(['id' => $section->id]));
    }

    public function testMoveUpAndDownSwapsOrder(): void
    {
        $app = $this->createLoggedAdmin('section-admin-move@example.com');
        $event = $this->getEvent($app);
        $a = $this->createProgrammeSection($app->getContainer(), $event, 'move a');
        $b = $this->createProgrammeSection($app->getContainer(), $event, 'move b');
        $c = $this->createProgrammeSection($app->getContainer(), $event, 'move c');
        $mine = [$a->id, $b->id, $c->id];

        $responseUp = $app->handle($this->createRequest(self::SECTIONS_URL . '/' . $c->id . '/move/up', 'POST'));
        self::assertSame(302, $responseUp->getStatusCode());
        self::assertSame([$a->id, $c->id, $b->id], $this->orderOf($app, $event, $mine));

        $app->handle($this->createRequest(self::SECTIONS_URL . '/' . $c->id . '/move/down', 'POST'));
        self::assertSame([$a->id, $b->id, $c->id], $this->orderOf($app, $event, $mine));
    }

    public function testMovePastTheTopIsNoop(): void
    {
        $app = $this->createLoggedAdmin('section-admin-move-top@example.com');
        $event = $this->getEvent($app);
        $this->createProgrammeSection($app->getContainer(), $event, 'some section');
        $repository = $this->getService($app, ProgrammeSectionRepository::class);
        $idsBefore = array_map(static fn (ProgrammeSection $s): int => $s->id, $repository->findForEvent($event));
        $topId = $idsBefore[0];
        $bottomId = $idsBefore[count($idsBefore) - 1];

        $responseTop = $app->handle($this->createRequest(self::SECTIONS_URL . '/' . $topId . '/move/up', 'POST'));
        $responseBottom = $app->handle($this->createRequest(
            self::SECTIONS_URL . '/' . $bottomId . '/move/down',
            'POST',
        ));

        self::assertSame(302, $responseTop->getStatusCode());
        self::assertSame(302, $responseBottom->getStatusCode());
        $idsAfter = array_map(static fn (ProgrammeSection $s): int => $s->id, $repository->findForEvent($event));
        self::assertSame($idsBefore, $idsAfter);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function foreignSectionRoutes(): array
    {
        return [
            'edit' => ['/edit'],
            'move' => ['/move/up'],
            'delete' => ['/delete'],
        ];
    }

    #[DataProvider('foreignSectionRoutes')]
    public function testSectionOfAnotherEventIsNotFound(string $routeSuffix): void
    {
        $app = $this->createLoggedAdmin('section-admin-foreign@example.com');
        $otherEvent = $this->getSmallTestEvent($this->getService($app, EventRepository::class));
        $foreign = $this->createProgrammeSection($app->getContainer(), $otherEvent, 'foreign section');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Programme section not found for event');
        $app->handle($this->createRequest(
            self::SECTIONS_URL . '/' . $foreign->id . $routeSuffix,
            'POST',
            ['name' => 'hijacked', 'subtitle' => 'hijacked', 'isExclusive' => '1'],
        ));
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function getEvent(App $app): Event
    {
        return $this->getService($app, EventRepository::class)->getOneBy(['slug' => 'test-event-slug']);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function findSectionByName(App $app, string $name): ?ProgrammeSection
    {
        $sections = $this->getService($app, ProgrammeSectionRepository::class)->findForEvent($this->getEvent($app));
        foreach ($sections as $section) {
            if ($section->name === $name) {
                return $section;
            }
        }

        return null;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function reloadSection(App $app, ProgrammeSection $section): ProgrammeSection
    {
        $reloaded = $this->getService($app, ProgrammeSectionRepository::class)->get($section->id);
        self::assertInstanceOf(ProgrammeSection::class, $reloaded);

        return $reloaded;
    }

    /**
     * @param App<ContainerInterface> $app
     * @param list<int> $ids
     * @return list<int>
     */
    private function orderOf(App $app, Event $event, array $ids): array
    {
        $ordered = [];
        foreach ($this->getService($app, ProgrammeSectionRepository::class)->findForEvent($event) as $section) {
            if (in_array($section->id, $ids, true)) {
                $ordered[] = $section->id;
            }
        }

        return $ordered;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createProgramme(App $app, Event $event, ProgrammeSection $section, bool $softDeleted): Programme
    {
        $programme = new Programme();
        $programme->name = 'section programme';
        $programme->description = 'description';
        $programme->place = 'somewhere';
        $programme->imageFilename = null;
        $programme->start = DateTimeUtils::getDateTime('2027-06-05 10:00');
        $programme->end = DateTimeUtils::getDateTime('2027-06-05 12:00');
        $programme->capacity = 5;
        $programme->isPreregistered = false;
        $programme->event = $event;
        $programme->section = $section;
        if ($softDeleted) {
            $programme->deletedAt = DateTimeUtils::getDateTime('2027-01-01 10:00');
        }
        $programme->setTargetRoles([ParticipantRole::Ist]);
        $this->getService($app, ProgrammeRepository::class)->persist($programme);

        return $programme;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function register(App $app, Programme $programme, Participant $participant): ProgrammeRegistration
    {
        $registration = new ProgrammeRegistration();
        $registration->programme = $programme;
        $registration->participant = $participant;
        $this->getService($app, ProgrammeRegistrationRepository::class)->persist($registration);

        return $registration;
    }

    /**
     * @return App<ContainerInterface>
     */
    private function createLoggedAdmin(string $email): App
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->getOneBy(['slug' => 'test-event-slug']);
        $user = $this->getService($app, UserService::class)->registerEmailUser($email, $event);
        $user->role = UserRole::Admin;
        $this->getService($app, UserRepository::class)->persist($user);

        $loginToken = new LoginToken();
        $loginToken->token = bin2hex(random_bytes(16));
        $loginToken->user = $user;
        $loginToken->used = false;
        $this->getService($app, LoginTokenRepository::class)->persist($loginToken);

        $response = $app->handle($this->createRequest('/v2/event/test-event-slug/tryLogin/' . $loginToken->token));
        self::assertSame(302, $response->getStatusCode());

        return $this->getTestApp(false);
    }
}
