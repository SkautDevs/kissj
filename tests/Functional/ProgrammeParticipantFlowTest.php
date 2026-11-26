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
    private const string DASHBOARD_PATH = '/v2/event/test-event-slug/participant/dashboard';

    public function testRemovedParticipantProgrammePagesAnswer404(): void
    {
        $app = $this->getTestApp();
        $programme = $this->createProgramme($app, 'removed pages workshop', null, 3);
        $ist = $this->createPaidIstWithEmail($app, 'flow-removed-ist@example.com');
        $app = $this->loginAs($app, $ist->getUserButNotNull());

        foreach (['', '/' . $programme->id, '/' . $programme->id . '/image'] as $suffix) {
            try {
                $app->handle($this->createRequest('/v2/event/test-event-slug/programmes' . $suffix));
                self::fail('route still exists: ' . $suffix);
            } catch (HttpNotFoundException $exception) {
                self::assertSame(404, $exception->getCode(), $suffix);
            }
        }
    }

    public function testDashboardWidgetLinksToEventApp(): void
    {
        $this->setEnv('EVENT_APP_URL', 'https://app.example.org/');
        $app = $this->getTestApp();
        $this->setEventApiKey($app, 'flow-widget-key');
        $programme = $this->createProgramme($app, 'widget link workshop', null, 3);
        $ist = $this->createPaidIstWithEmail($app, 'widget-link-ist@example.com');
        $this->getService($app, ProgrammeService::class)->adminAssign($programme, $ist);
        $app = $this->loginAs($app, $ist->getUserButNotNull());

        $body = (string)$app->handle($this->createRequest(self::DASHBOARD_PATH))->getBody();

        self::assertStringContainsString('widget link workshop', $body);
        self::assertMatchesRegularExpression(
            '~<a class="btn" href="https://app\.example\.org/test-event-slug/" target="_blank" rel="noopener">\s*'
            . preg_quote($this->translate($app, 'programme.widget.all'), '~') . '\s*</a>~',
            $body,
        );
    }

    public function testDashboardWidgetHasNoButtonWithoutEventAppUrl(): void
    {
        $this->setEnv('EVENT_APP_URL', '');
        $app = $this->getTestApp();
        $this->setEventApiKey($app, 'flow-widget-key');
        $programme = $this->createProgramme($app, 'widget no url workshop', null, 3);
        $ist = $this->createPaidIstWithEmail($app, 'widget-no-url-ist@example.com');
        $this->getService($app, ProgrammeService::class)->adminAssign($programme, $ist);
        $app = $this->loginAs($app, $ist->getUserButNotNull());

        $body = (string)$app->handle($this->createRequest(self::DASHBOARD_PATH))->getBody();

        self::assertStringContainsString('widget no url workshop', $body);
        self::assertStringNotContainsString($this->translate($app, 'programme.widget.all'), $body);
    }

    public function testDashboardWidgetHasNoButtonOnEventWithoutApiKey(): void
    {
        $this->setEnv('EVENT_APP_URL', 'https://app.example.org/');
        $app = $this->getTestApp();
        $programme = $this->createProgramme($app, 'widget no key workshop', null, 3);
        $ist = $this->createPaidIstWithEmail($app, 'widget-no-key-ist@example.com');
        $this->getService($app, ProgrammeService::class)->adminAssign($programme, $ist);
        $app = $this->loginAs($app, $ist->getUserButNotNull());

        $body = (string)$app->handle($this->createRequest(self::DASHBOARD_PATH))->getBody();

        self::assertStringContainsString('widget no key workshop', $body);
        self::assertStringNotContainsString($this->translate($app, 'programme.widget.all'), $body);
    }

    public function testDashboardWidgetHintsEventAppWhenOpen(): void
    {
        $app = $this->getTestApp();
        $this->createProgramme($app, 'widget workshop', null, 3);
        $ist = $this->createPaidIstWithEmail($app, 'widget-open-ist@example.com');
        $this->setProgrammeWindow($app, '-1 day', '+1 day');
        $app = $this->loginAs($app, $ist->getUserButNotNull());

        $response = $app->handle($this->createRequest(self::DASHBOARD_PATH));

        self::assertSame(200, $response->getStatusCode());
        $body = (string)$response->getBody();
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

        $response = $app->handle($this->createRequest(self::DASHBOARD_PATH));

        self::assertSame(200, $response->getStatusCode());
        $body = (string)$response->getBody();
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

        $response = $app->handle($this->createRequest(self::DASHBOARD_PATH));

        self::assertSame(200, $response->getStatusCode());
        self::assertStringNotContainsString('programme-eventapp-hint', (string)$response->getBody());
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function setEventApiKey(App $app, string $key): void
    {
        $event = $this->getService($app, EventRepository::class)->getOneBy(['slug' => self::TEST_EVENT_SLUG]);
        $this->mutateEventForTest($app->getContainer(), $event, ['apiKeyEvent' => $key]);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function setProgrammeWindow(App $app, ?string $start, ?string $end): void
    {
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->getOneBy(['slug' => self::TEST_EVENT_SLUG]);
        $event->programmeEnrolmentStart = $start === null ? null : DateTimeUtils::getDateTime($start);
        $event->programmeEnrolmentEnd = $end === null ? null : DateTimeUtils::getDateTime($end);
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
        bool $isAutoenrolled = false,
    ): Programme {
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);

        $programme = new Programme();
        $programme->name = $name;
        $programme->description = 'description';
        $programme->start = DateTimeUtils::getDateTime($start);
        $programme->end = DateTimeUtils::getDateTime($end);
        $programme->capacity = $capacity;
        $programme->isAutoenrolled = $isAutoenrolled;
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
