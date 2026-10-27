<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\EventRepository;
use kissj\Participant\ParticipantRole;
use kissj\Participant\Participant;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeRepository;
use kissj\Programme\ProgrammeSection;
use kissj\Programme\ProgrammeService;
use kissj\User\LoginToken;
use kissj\User\LoginTokenRepository;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Tests\AppTestCase;

class ProgrammeParticipantFlowTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-event-slug';

    public function testPaidParticipantSeesReadOnlyProgrammesWithClosedWindow(): void
    {
        $app = $this->getTestApp();
        $programme = $this->createProgramme($app, 'visible workshop', null, 3);
        $this->createProgramme($app, 'opening ceremony', null, 1, '2027-06-05 08:00', '2027-06-05 09:00', null, true);
        $ist = $this->createPaidIstWithEmail($app, 'flow-ist@example.com');
        $this->setProgrammeWindow($app, null, null);
        $this->getService($app, ProgrammeService::class)->adminAssign($programme, $ist);
        $app = $this->loginAs($app, $ist->getUserButNotNull());

        $responseList = $app->handle($this->createRequest('/v2/event/test-event-slug/programmes'));
        self::assertSame(200, $responseList->getStatusCode());
        $body = (string)$responseList->getBody();
        self::assertStringContainsString('visible workshop', $body);
        self::assertStringContainsString('opening ceremony', $body);
        self::assertStringContainsString('programme-registered', $body);
        self::assertStringNotContainsString('programmes/' . $programme->id . '/register', $body);
        self::assertStringNotContainsString('programmes/' . $programme->id . '/unregister', $body);

        $responseDetail = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/programmes/' . $programme->id,
        ));
        self::assertSame(200, $responseDetail->getStatusCode());
        $detailBody = (string)$responseDetail->getBody();
        self::assertStringContainsString('programme-registered', $detailBody);
        self::assertStringNotContainsString('programmes/' . $programme->id . '/register', $detailBody);
        self::assertStringNotContainsString('programmes/' . $programme->id . '/unregister', $detailBody);
    }

    public function testUnpaidParticipantIsRedirectedFromProgrammes(): void
    {
        $app = $this->getTestApp();
        $programme = $this->createProgramme($app, 'unpaid workshop', null, 3);
        $user = $this->registerUser($app, 'flow-unpaid-ist@example.com');
        $this->getService($app, UserService::class)->createParticipantSetRole($user, 'ist');
        $app = $this->loginAs($app, $user);

        foreach (['', '/' . $programme->id, '/' . $programme->id . '/image'] as $suffix) {
            $response = $app->handle($this->createRequest('/v2/event/test-event-slug/programmes' . $suffix));
            self::assertSame(302, $response->getStatusCode(), $suffix);
        }
    }

    public function testDetailAndImageOfProgrammeForOtherRoleReturn404(): void
    {
        $app = $this->getTestApp();
        $programme = $this->createProgramme(
            $app,
            'patrol leaders workshop',
            null,
            3,
            roles: [ParticipantRole::PatrolLeader],
        );
        $ist = $this->createPaidIstWithEmail($app, 'flow-other-role-ist@example.com');
        $app = $this->loginAs($app, $ist->getUserButNotNull());

        $responseDetail = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/programmes/' . $programme->id,
        ));
        self::assertSame(404, $responseDetail->getStatusCode());

        $responseImage = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/programmes/' . $programme->id . '/image',
        ));
        self::assertSame(404, $responseImage->getStatusCode());
    }

    public function testRegisterRoutesAreGone(): void
    {
        $app = $this->getTestApp();
        $programme = $this->createProgramme($app, 'gone routes workshop', null, 3);
        $ist = $this->createPaidIstWithEmail($app, 'flow-gone-ist@example.com');
        $app = $this->loginAs($app, $ist->getUserButNotNull());

        foreach (['register', 'unregister'] as $action) {
            try {
                $app->handle($this->createRequest(
                    '/v2/event/test-event-slug/programmes/' . $programme->id . '/' . $action,
                    'POST',
                ));
                self::fail($action . ' route still exists');
            } catch (HttpNotFoundException $exception) {
                self::assertSame(404, $exception->getCode(), $action);
            }
        }
    }

    public function testProgrammeImageResponseCarriesSecurityHeaders(): void
    {
        $app = $this->getTestApp();
        $programme = $this->createProgramme($app, 'image workshop', null, 3);
        $storedFilename = bin2hex(random_bytes(16)); // 32-hex, passes the image route guard
        $programme->imageFilename = $storedFilename;
        $this->getService($app, ProgrammeRepository::class)->persist($programme);

        $uploadPath = __DIR__ . '/../../uploads/' . $storedFilename;
        $image = imagecreatetruecolor(1, 1);
        imagepng($image, $uploadPath);
        imagedestroy($image);

        $ist = $this->createPaidIstWithEmail($app, 'image-flow-ist@example.com');
        $app = $this->loginAs($app, $ist->getUserButNotNull());

        try {
            $response = $app->handle($this->createRequest(
                '/v2/event/test-event-slug/programmes/' . $programme->id . '/image',
            ));
            self::assertSame(200, $response->getStatusCode());
            self::assertSame('nosniff', $response->getHeaderLine('X-Content-Type-Options'));
            self::assertStringContainsString("script-src 'none'", $response->getHeaderLine('Content-Security-Policy'));
        } finally {
            @unlink($uploadPath);
        }
    }

    public function testDetailOfMissingProgrammeReturns404(): void
    {
        $app = $this->getTestApp();
        $ist = $this->createPaidIstWithEmail($app, 'detail-missing-ist@example.com');
        $app = $this->loginAs($app, $ist->getUserButNotNull());

        // a user-supplied id for a programme that does not exist must be a clean 404,
        // not a 500 + Sentry event from the repository's RuntimeException
        $response = $app->handle($this->createRequest('/v2/event/test-event-slug/programmes/999999'));
        self::assertSame(404, $response->getStatusCode());
    }

    public function testDetailOfDeletedProgrammeReturns404(): void
    {
        $app = $this->getTestApp();
        $programme = $this->createProgramme($app, 'deleted workshop', null, 3);
        $ist = $this->createPaidIstWithEmail($app, 'detail-deleted-ist@example.com');
        $app = $this->loginAs($app, $ist->getUserButNotNull());

        $this->getService($app, ProgrammeService::class)->deleteProgramme($programme);

        $response = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/programmes/' . $programme->id,
        ));
        self::assertSame(404, $response->getStatusCode());
    }

    public function testImageOfMissingProgrammeReturns404(): void
    {
        $app = $this->getTestApp();
        $ist = $this->createPaidIstWithEmail($app, 'image-missing-ist@example.com');
        $app = $this->loginAs($app, $ist->getUserButNotNull());

        $response = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/programmes/999999/image',
        ));
        self::assertSame(404, $response->getStatusCode());
    }

    public function testDashboardWidgetLinksPaidParticipantToProgrammesAndHintsEventAppWhenOpen(): void
    {
        $app = $this->getTestApp();
        $this->createProgramme($app, 'widget workshop', null, 3);
        $ist = $this->createPaidIstWithEmail($app, 'widget-open-ist@example.com');
        $this->setProgrammeWindow($app, '-1 day', '+1 day');
        $app = $this->loginAs($app, $ist->getUserButNotNull());

        $response = $app->handle($this->createRequest('/v2/event/test-event-slug/participant/dashboard'));

        self::assertSame(200, $response->getStatusCode());
        $body = (string)$response->getBody();
        self::assertStringContainsString('href="/v2/event/test-event-slug/programmes"', $body);
        self::assertStringContainsString('programme-eventapp-hint', $body);
    }

    public function testDashboardWidgetHidesHintWhenWindowClosed(): void
    {
        $app = $this->getTestApp();
        $programme = $this->createProgramme($app, 'widget closed workshop', null, 3);
        $ist = $this->createPaidIstWithEmail($app, 'widget-closed-ist@example.com');
        $this->setProgrammeWindow($app, null, null);
        $this->getService($app, ProgrammeService::class)->adminAssign($programme, $ist);
        $app = $this->loginAs($app, $ist->getUserButNotNull());

        $response = $app->handle($this->createRequest('/v2/event/test-event-slug/participant/dashboard'));

        self::assertSame(200, $response->getStatusCode());
        $body = (string)$response->getBody();
        self::assertStringContainsString('href="/v2/event/test-event-slug/programmes"', $body);
        self::assertStringNotContainsString('programme-eventapp-hint', $body);
    }

    public function testDashboardWidgetHiddenForUnpaidParticipantDuringOpenWindow(): void
    {
        $app = $this->getTestApp();
        $this->createProgramme($app, 'widget unpaid workshop', null, 3);
        $user = $this->registerUser($app, 'widget-unpaid-ist@example.com');
        $this->getService($app, UserService::class)->createParticipantSetRole($user, 'ist');
        $this->setProgrammeWindow($app, '-1 day', '+1 day');
        $app = $this->loginAs($app, $user);

        $response = $app->handle($this->createRequest('/v2/event/test-event-slug/participant/dashboard'));

        self::assertSame(200, $response->getStatusCode());
        self::assertStringNotContainsString(
            'href="/v2/event/test-event-slug/programmes"',
            (string)$response->getBody(),
        );
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function setProgrammeWindow(App $app, ?string $start, ?string $end): void
    {
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->getOneBy(['slug' => self::TEST_EVENT_SLUG]);
        $event->programmeRegistrationStart = $start === null ? null : DateTimeUtils::getDateTime($start);
        $event->programmeRegistrationEnd = $end === null ? null : DateTimeUtils::getDateTime($end);
        $eventRepository->persist($event);
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
        $programme->event = $eventRepository->getOneBy(['slug' => self::TEST_EVENT_SLUG]);
        $programme->section = $section
            ?? $this->createProgrammeSection($app->getContainer(), $programme->event, 'Program');
        $programme->setTargetRoles($roles ?? [
            ParticipantRole::Ist,
            ParticipantRole::TroopLeader,
            ParticipantRole::TroopParticipant,
        ]);
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
