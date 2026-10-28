<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\BankPayment\BankPaymentRepository;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\FlashMessages\FlashMessagesInterface;
use kissj\Participant\ParticipantRepository;
use kissj\Payment\PaymentRepository;
use kissj\Payment\PaymentStatus;
use kissj\Participant\ParticipantRole;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeRepository;
use kissj\Programme\ProgrammeSectionRepository;
use kissj\User\User;
use kissj\User\UserLoginType;
use kissj\User\UserRepository;
use kissj\User\UserRole;
use kissj\User\UserStatus;
use LogicException;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Slim\App;
use Slim\Interfaces\RouteInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tests\AppTestCase;
use Throwable;

class CrossEventGuardTest extends AppTestCase
{
    private const string OWN_EVENT_SLUG = 'test-event-slug';

    private const array EVENT_ROUTE_PREFIXES = [
        '/v2/event/{eventSlug}/',
        '/v3/event/{eventSlug}/',
    ];

    private const array ADMIN_ROUTE_PREFIXES = [
        '/v2/event/{eventSlug}/admin',
        '/v3/event/{eventSlug}/admin',
    ];

    private const string BANK_PAYMENT_PATTERN_FRAGMENT = '/payments/auto/setPayment';

    private const array FILLER_PLACEHOLDERS = [
        '{dealSlug}' => '1',
    ];

    private const array WITHOUT_ROLE_ROUTE_NAMES = ['chooseRole', 'setRole'];

    private const array NOT_FOUND_STATUS_ROUTE_NAMES = ['admin-programme-image'];

    private const array REQUEST_BODY = [
        'reason' => 'cross-event',
        'notice' => 'cross-event',
        'newPrice' => '999',
        'price' => '999',
        'role' => 'ist',
        'adminNote' => 'cross-event',
        'subcamp' => 'detail.subcamp.red',
        'internalUniqueId' => 'cross-event',
        'internalCommonId' => 'cross-event',
        'firstName' => 'cross-event',
    ];

    public function testUserOfOtherEventIsLoggedOutOnEveryEventRoute(): void
    {
        $app = $this->getTestApp();
        $openAdmin = $this->createForeignAdmin($app, UserStatus::Open);
        $withoutRoleAdmin = $this->createForeignAdmin($app, UserStatus::WithoutRole);
        $landingUrl = $app->getRouteCollector()->getRouteParser()
            ->urlFor('landing', ['eventSlug' => self::OWN_EVENT_SLUG]);

        $failures = [];
        $checkedCount = 0;
        $checkedRouteNames = [];
        foreach ($app->getRouteCollector()->getRoutes() as $route) {
            if ($this->isEventScopedRoute($route->getPattern()) === false) {
                continue;
            }

            $checkedRouteNames[] = $route->getName();
            $foreignAdmin = in_array($route->getName(), self::WITHOUT_ROLE_ROUTE_NAMES, true)
                ? $withoutRoleAdmin
                : $openAdmin;
            $path = $this->fillPlaceholders($route->getPattern());
            foreach ($route->getMethods() as $method) {
                if ($method === 'OPTIONS') {
                    continue;
                }

                $checkedCount++;
                $failure = $this->findLogoutFailure($app, $route, $method, $path, $foreignAdmin, $landingUrl);
                if ($failure !== null) {
                    $failures[] = $failure;
                }
            }
        }

        self::assertSame([], $failures, 'user of other event was not logged out on an event route');
        self::assertGreaterThanOrEqual(
            100,
            $checkedCount,
            'too few routes checked, the route list or prefixes are broken',
        );
        foreach (self::WITHOUT_ROLE_ROUTE_NAMES as $routeName) {
            self::assertContains($routeName, $checkedRouteNames, 'without role route does not exist: ' . $routeName);
        }
    }

    public function testAdminCannotTouchRecordsOfOtherEventById(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer() ?? throw new LogicException('container missing');
        $this->setEventType($container, 'navigamus', self::OWN_EVENT_SLUG);
        $admin = $this->createAdminUser($app);
        $ownEvent = $this->getService($app, EventRepository::class)->findBySlug(self::OWN_EVENT_SLUG)
            ?? throw new LogicException('seeded event ' . self::OWN_EVENT_SLUG . ' not found');
        $this->assertOwnParticipantIsReachable($app, $container, $admin, $ownEvent);
        $fixture = $this->createForeignFixture($app);

        $failures = [];
        $checkedCount = 0;
        foreach ($this->getIdTakingAdminRoutes($app) as $route) {
            $path = $this->fillIdPlaceholders($route->getPattern(), $fixture);
            foreach ($route->getMethods() as $method) {
                if ($method === 'OPTIONS') {
                    continue;
                }

                $checkedCount++;
                $failure = $this->findIdRequestFailure($app, $route, $method, $path, $admin, $fixture);
                if ($failure !== null) {
                    $failures[] = $failure;
                }
            }
        }

        self::assertSame([], $failures, 'admin request by id of other event was not rejected by event scoping');
        self::assertGreaterThanOrEqual(
            25,
            $checkedCount,
            'too few id requests checked, the route list or prefixes are broken',
        );
    }

    private function isEventScopedRoute(string $pattern): bool
    {
        if (in_array($pattern, RouteGuardTest::PUBLIC_EXACT_PATTERNS, true)) {
            return false;
        }

        foreach (self::EVENT_ROUTE_PREFIXES as $prefix) {
            if (str_starts_with($pattern, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createForeignAdmin(App $app, UserStatus $status): User
    {
        $admin = new User();
        $admin->event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $admin->role = UserRole::Admin;
        $admin->email = 'cross-event-admin-' . $status->value . '@example.com';
        $admin->loginType = UserLoginType::Email;
        $admin->status = $status;
        $this->getService($app, UserRepository::class)->persist($admin);

        return $admin;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function findLogoutFailure(
        App $app,
        RouteInterface $route,
        string $method,
        string $path,
        User $foreignAdmin,
        string $landingUrl,
    ): ?string {
        $label = sprintf(
            '%s %s %s as %s',
            $route->getName() ?? '(unnamed)',
            $method,
            $path,
            $foreignAdmin->status->value,
        );

        $routeMismatch = $this->findRouteMismatch($app->getRouteResolver(), $route, $method, $path, $label);
        if ($routeMismatch !== null) {
            return $routeMismatch;
        }

        $_SESSION = ['user' => ['id' => $foreignAdmin->id]];
        $requestApp = $this->getTestApp(false);
        try {
            $response = $requestApp->handle($this->createRequest($path, $method));
        } catch (Throwable $exception) {
            return sprintf('%s: threw %s: %s', $label, $exception::class, $exception->getMessage());
        }

        $status = $response->getStatusCode();
        $location = $response->getHeaderLine('Location');
        if ($status !== 302 || $location !== $landingUrl) {
            return sprintf('%s: got %d, Location "%s"', $label, $status, $location);
        }

        if (isset($_SESSION['user'])) {
            return sprintf('%s: session user was not cleared', $label);
        }

        if ($this->hasFlash($requestApp, 'flash.warning.wrongEvent') === false) {
            return sprintf('%s: wrongEvent flash missing', $label);
        }

        return null;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createForeignFixture(App $app): CrossEventForeignFixture
    {
        $container = $app->getContainer() ?? throw new LogicException('container missing');
        $foreignEvent = $this->getTestSlugEvent($this->getService($app, EventRepository::class));

        $participant = $this->createParticipantWithRole($container, 'tp', $foreignEvent);
        $user = $participant->getUserButNotNull();
        $user->status = UserStatus::Closed;
        $this->getService($app, UserRepository::class)->persist($user);
        $payment = $this->createFinancesPayment(
            $app,
            $foreignEvent,
            PaymentStatus::Waiting,
            '100',
            participant: $participant,
        );
        $bankPayment = $this->createBankPayment($this->getService($app, BankPaymentRepository::class), $foreignEvent);
        $programme = $this->createProgramme($app, $foreignEvent);

        return new CrossEventForeignFixture(
            $participant->id,
            $user->id,
            $payment->id,
            $bankPayment->id,
            $programme->id,
            $programme->section->id,
        );
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createProgramme(App $app, Event $event): Programme
    {
        $programme = new Programme();
        $programme->name = 'cross-event';
        $programme->description = 'cross-event';
        $programme->place = 'cross-event';
        $programme->imageFilename = 'abcdef0123456789abcdef0123456789';
        $programme->start = DateTimeUtils::getDateTime('2027-06-05 10:00');
        $programme->end = DateTimeUtils::getDateTime('2027-06-05 12:00');
        $programme->capacity = 10;
        $programme->isPreregistered = false;
        $programme->event = $event;
        $programme->section = $this->createProgrammeSection($app->getContainer(), $event, 'cross-event');
        $programme->setTargetRoles([ParticipantRole::Ist]);
        $this->getService($app, ProgrammeRepository::class)->persist($programme);

        return $programme;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function assertOwnParticipantIsReachable(
        App $app,
        ContainerInterface $container,
        User $admin,
        Event $ownEvent,
    ): void {
        $ownParticipant = $this->createParticipantWithRole($container, 'tp', $ownEvent);
        $path = $app->getRouteCollector()->getRouteParser()->urlFor(
            'admin-show-participant-details-changeable',
            ['eventSlug' => self::OWN_EVENT_SLUG, 'participantId' => (string)$ownParticipant->id],
        );

        $_SESSION = ['user' => ['id' => $admin->id]];
        $response = $this->getTestApp(false)->handle($this->createRequest($path));

        self::assertSame(200, $response->getStatusCode(), 'admin cannot reach a participant of own event');
    }

    /**
     * @param App<ContainerInterface> $app
     * @return list<RouteInterface>
     */
    private function getIdTakingAdminRoutes(App $app): array
    {
        $idRoutes = [];
        foreach ($app->getRouteCollector()->getRoutes() as $route) {
            if ($this->isAdminRoute($route->getPattern()) && $this->takesId($route->getPattern())) {
                $idRoutes[] = $route;
            }
        }

        return $idRoutes;
    }

    private function isAdminRoute(string $pattern): bool
    {
        foreach (self::ADMIN_ROUTE_PREFIXES as $prefix) {
            if (str_starts_with($pattern, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function takesId(string $pattern): bool
    {
        $withoutEvent = str_replace('{eventSlug}', '', $pattern);
        $placeholderCount = preg_match_all('/\{[^}]+\}/', $withoutEvent);
        if ($placeholderCount === 0) {
            return false;
        }

        $this->classifyId($pattern);

        return true;
    }

    private function classifyId(string $pattern): CrossEventIdKind
    {
        if (str_contains($pattern, '{programmeId}')) {
            return CrossEventIdKind::Programme;
        }
        if (str_contains($pattern, '{sectionId}')) {
            return CrossEventIdKind::ProgrammeSection;
        }

        $hasParticipantId = str_contains($pattern, '{participantId}');
        $hasPaymentId = str_contains($pattern, '{paymentId}');
        if ($hasParticipantId && $hasPaymentId) {
            throw new LogicException('admin route ' . $pattern . ' takes both participant and payment id');
        }
        if ($hasParticipantId) {
            return CrossEventIdKind::Participant;
        }
        if ($hasPaymentId && str_contains($pattern, self::BANK_PAYMENT_PATTERN_FRAGMENT)) {
            return CrossEventIdKind::BankPayment;
        }
        if ($hasPaymentId) {
            return CrossEventIdKind::Payment;
        }

        throw new LogicException('admin route ' . $pattern . ' takes an id of unknown kind');
    }

    private function fillIdPlaceholders(string $pattern, CrossEventForeignFixture $fixture): string
    {
        $paymentId = $this->classifyId($pattern) === CrossEventIdKind::BankPayment
            ? $fixture->bankPaymentId
            : $fixture->paymentId;
        $filled = strtr($pattern, [
            '{eventSlug}' => self::OWN_EVENT_SLUG,
            '{participantId}' => (string)$fixture->participantId,
            '{paymentId}' => (string)$paymentId,
            '{programmeId}' => (string)$fixture->programmeId,
            '{sectionId}' => (string)$fixture->programmeSectionId,
            '{direction:up|down}' => 'up',
            ...self::FILLER_PLACEHOLDERS,
        ]);
        if (preg_match('/\{[^}]+\}/', $filled) === 1) {
            throw new LogicException('admin route ' . $pattern . ' has a placeholder without a filler');
        }

        return $filled;
    }

    private function getExpectedNotFoundMessage(string $pattern, CrossEventForeignFixture $fixture): string
    {
        return match ($this->classifyId($pattern)) {
            CrossEventIdKind::Participant => sprintf('Participant with ID %d not found', $fixture->participantId),
            CrossEventIdKind::Payment => sprintf('Payment with ID %d not found', $fixture->paymentId),
            CrossEventIdKind::BankPayment => sprintf('Bank payment with ID %d not found', $fixture->bankPaymentId),
            CrossEventIdKind::Programme => 'Programme not found for event',
            CrossEventIdKind::ProgrammeSection => 'Programme section not found for event',
        };
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function findIdRequestFailure(
        App $app,
        RouteInterface $route,
        string $method,
        string $path,
        User $admin,
        CrossEventForeignFixture $fixture,
    ): ?string {
        $label = sprintf('%s %s %s', $route->getName() ?? '(unnamed)', $method, $path);

        $routeMismatch = $this->findRouteMismatch($app->getRouteResolver(), $route, $method, $path, $label);
        if ($routeMismatch !== null) {
            return $routeMismatch;
        }

        $participantBefore = $this->getParticipantRow($fixture);
        $userBefore = $this->getUserRow($fixture);
        $paymentBefore = $this->getPaymentRow($fixture);
        $bankPaymentBefore = $this->getBankPaymentRow($fixture);
        $programmeBefore = $this->getProgrammeRow($fixture);
        $sectionBefore = $this->getProgrammeSectionRow($fixture);

        $rejection = $this->findRejectionFailure($route, $method, $path, $admin, $label, $fixture);

        if ($this->getParticipantRow($fixture) !== $participantBefore) {
            return sprintf('%s: foreign participant changed', $label);
        }
        if ($this->getUserRow($fixture) !== $userBefore) {
            return sprintf('%s: foreign user changed', $label);
        }
        if ($this->getPaymentRow($fixture) !== $paymentBefore) {
            return sprintf('%s: foreign payment changed', $label);
        }
        if ($this->getBankPaymentRow($fixture) !== $bankPaymentBefore) {
            return sprintf('%s: foreign bank payment changed', $label);
        }
        if ($this->getProgrammeRow($fixture) !== $programmeBefore) {
            return sprintf('%s: foreign programme changed', $label);
        }
        if ($this->getProgrammeSectionRow($fixture) !== $sectionBefore) {
            return sprintf('%s: foreign programme section changed', $label);
        }

        return $rejection;
    }

    private function findRejectionFailure(
        RouteInterface $route,
        string $method,
        string $path,
        User $admin,
        string $label,
        CrossEventForeignFixture $fixture,
    ): ?string {
        $_SESSION = ['user' => ['id' => $admin->id]];
        $requestApp = $this->getTestApp(false);
        try {
            $body = $method === 'POST' ? self::REQUEST_BODY : [];
            $response = $requestApp->handle($this->createRequest($path, $method, $body));
        } catch (RuntimeException $exception) {
            if ($exception->getMessage() === $this->getExpectedNotFoundMessage($route->getPattern(), $fixture)) {
                return null;
            }

            return sprintf('%s: threw %s: %s', $label, $exception::class, $exception->getMessage());
        } catch (Throwable $exception) {
            return sprintf('%s: threw %s: %s', $label, $exception::class, $exception->getMessage());
        }

        $status = $response->getStatusCode();
        if ($status >= 200 && $status < 300) {
            return sprintf('%s: got %d', $label, $status);
        }

        if ($status === 404 && in_array($route->getName(), self::NOT_FOUND_STATUS_ROUTE_NAMES, true)) {
            return null;
        }

        return sprintf(
            '%s: got %d, Location "%s" without a not-found rejection',
            $label,
            $status,
            $response->getHeaderLine('Location'),
        );
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function hasFlash(App $app, string $flashKey): bool
    {
        $expectedMessage = $this->getService($app, TranslatorInterface::class)->trans($flashKey);
        foreach ($this->getService($app, FlashMessagesInterface::class)->dumpMessagesIntoArray() as $message) {
            if ($message['message'] === $expectedMessage) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    private function getParticipantRow(CrossEventForeignFixture $fixture): array
    {
        return $this->getService($this->getTestApp(false), ParticipantRepository::class)
            ->get($fixture->participantId)->getRowData();
    }

    /**
     * @return array<string, mixed>
     */
    private function getUserRow(CrossEventForeignFixture $fixture): array
    {
        return $this->getService($this->getTestApp(false), UserRepository::class)
            ->get($fixture->userId)->getRowData();
    }

    /**
     * @return array<string, mixed>
     */
    private function getPaymentRow(CrossEventForeignFixture $fixture): array
    {
        return $this->getService($this->getTestApp(false), PaymentRepository::class)
            ->get($fixture->paymentId)->getRowData();
    }

    /**
     * @return array<string, mixed>
     */
    private function getBankPaymentRow(CrossEventForeignFixture $fixture): array
    {
        return $this->getService($this->getTestApp(false), BankPaymentRepository::class)
            ->get($fixture->bankPaymentId)->getRowData();
    }

    /**
     * @return array<string, mixed>
     */
    private function getProgrammeRow(CrossEventForeignFixture $fixture): array
    {
        return $this->getService($this->getTestApp(false), ProgrammeRepository::class)
            ->get($fixture->programmeId)->getRowData();
    }

    /**
     * @return array<string, mixed>
     */
    private function getProgrammeSectionRow(CrossEventForeignFixture $fixture): array
    {
        return $this->getService($this->getTestApp(false), ProgrammeSectionRepository::class)
            ->get($fixture->programmeSectionId)->getRowData();
    }
}
