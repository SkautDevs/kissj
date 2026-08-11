<?php

declare(strict_types=1);

namespace Tests;

use ArrayObject;
use kissj\Application\ApplicationGetter;
use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Mailer\MailerSettings;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\User\User;
use kissj\User\UserLoginType;
use kissj\User\UserRepository;
use kissj\User\UserRole;
use kissj\User\UserService;
use kissj\User\UserStatus;
use LeanMapper\Connection;
use LogicException;
use Phinx\Console\PhinxApplication;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Slim\App;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request;
use Slim\Psr7\Uri;
use Slim\Views\Twig;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\Event\SentMessageEvent;

class AppTestCase extends TestCase
{
    public const string DB_FILENAME = 'db_tests.sqlite';
    public const string DB_TEMPLATE_FILENAME = 'db_template.sqlite';

    // full clearTempFolder() wipe happens once per process: it clears stale same-pid
    // leftovers (pid reuse after a crash) before the first template snapshot exists;
    // later fresh inits must keep the template and the compiled DI container.
    // a process leading with getTestApp(false) migrates before the wipe, so its first
    // fresh init re-migrates once - accepted waste, semantics unharmed
    private static bool $runDirInitialized = false;

    /** @var callable|null */
    private $originalErrorHandler = null;

    /** @var callable|null */
    private $originalExceptionHandler = null;

    // getTestApp() builds a brand-new DI container (and thus a new pg_connect()) every call;
    // nothing else ever closes them, so across the whole suite that exhausts Postgres's
    // max_connections. Track every connection a test creates and close it in tearDown().
    /** @var Connection[] */
    private array $connectionsToClose = [];

    /** @var array{container: ContainerInterface, eventId: int, originals: array<string, mixed>}|null */
    private ?array $mutatedEventRestore = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Save original handlers before test runs
        // Note: set_error_handler returns the previous handler
        $this->originalErrorHandler = set_error_handler(fn () => false);
        restore_error_handler();

        $this->originalExceptionHandler = set_exception_handler(fn () => null);
        restore_exception_handler();
    }

    protected function tearDown(): void
    {
        // Destroy session to ensure clean state between tests
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }

        $this->restoreMutatedEventFields();

        // Restore original handlers after test completes
        // This cleans up any handlers registered by Whoops or other middleware
        while (true) {
            $current = set_error_handler(fn () => false);
            restore_error_handler();
            if ($current === $this->originalErrorHandler || $current === null) {
                break;
            }
            restore_error_handler();
        }

        while (true) {
            $current = set_exception_handler(fn () => null);
            restore_exception_handler();
            if ($current === $this->originalExceptionHandler || $current === null) {
                break;
            }
            restore_exception_handler();
        }

        foreach ($this->connectionsToClose as $connection) {
            if ($connection->isConnected()) {
                $connection->disconnect();
            }
        }
        $this->connectionsToClose = [];

        parent::tearDown();
    }

    /**
     * @return App<ContainerInterface>
     */
    protected function getTestApp(bool $freshInit = true): App
    {
        $this->forceSqliteDbType();

        if ($freshInit) {
            if (!self::$runDirInitialized) {
                $this->clearTempFolder();
                self::$runDirInitialized = true;
            }

            // Properly destroy any active session before starting fresh
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_unset();
                session_destroy();
            }

            // Clear session superglobal
            $_SESSION = [];
        }

        $runTempPath = $this->getRunTempPath();
        if (!is_dir($runTempPath)) {
            mkdir($runTempPath, 0777, true);
        }

        // provide the db also when getTestApp(false) is a process's first call - the
        // per-process database does not exist yet
        if ($freshInit || !file_exists($runTempPath . '/' . self::DB_FILENAME)) {
            $this->provideCleanDatabase($runTempPath);
        }

        $app = (new ApplicationGetter())->getApp(
            __DIR__ . '/',
            'env.testing',
            $this->getRunTempPath()
        );

        $connection = $this->getService($app, Connection::class);
        if ($connection->getConfig('driver') !== 'sqlite') {
            throw new LogicException(
                'Functional tests must run on the per-test sqlite database — refusing to touch a real database.',
            );
        }
        $this->connectionsToClose[] = $connection;

        return $app;
    }

    // clean database per init without paying the migration chain each time:
    // migrate once per process, snapshot, then clone the snapshot
    private function provideCleanDatabase(string $runTempPath): void
    {
        $dbPath = $runTempPath . '/' . self::DB_FILENAME;
        $templatePath = $runTempPath . '/' . self::DB_TEMPLATE_FILENAME;

        // unlink instead of overwriting in place, so connections still open in a
        // previously booted app keep their own (old) inode
        if (!@unlink($dbPath) && file_exists($dbPath)) {
            throw new RuntimeException('unlink failed on ' . $dbPath);
        }

        // @: the template may vanish in a concurrent-prune race - fall through to migrating
        if (is_file($templatePath) && @copy($templatePath, $dbPath)) {
            return;
        }

        $arguments = [
            'command' => 'migrate',
            '--configuration' => __DIR__ . '/phinxConfiguration.php',
        ];

        // tripwire: if the DB_TYPE force-set above is ever removed or reordered,
        // fail before phinx applies DDL to a real database; under a config-driven
        // phpunit run the phpunit.xml env force makes this unreachable - it guards
        // config-less invocations (custom -c, IDE runners)
        if ($_ENV['DB_TYPE'] !== 'sqlite') {
            throw new LogicException('Test migrations must run on sqlite — refusing to migrate a real database.');
        }

        $phinx = new PhinxApplication();
        $phinx->setAutoExit(false);
        $phinxOutput = new BufferedOutput();
        if ($phinx->run(new ArrayInput($arguments), $phinxOutput) !== 0) {
            throw new RuntimeException('test database migration failed: ' . $phinxOutput->fetch());
        }

        if (!copy($dbPath, $templatePath)) {
            throw new RuntimeException('snapshotting the template database failed');
        }
    }

    // the container env pins DB_TYPE=postgresql and phpdotenv never overrides
    // existing vars - force sqlite for both phinx and the app connection;
    // DATABASE_PATH keeps Settings.php and tests/phinxConfiguration.php pointed
    // at the same per-process file instead of deriving it twice.
    // do not inline into getTestApp(): the pre-migration tripwire relies on this
    // assignment being statically opaque, or PHPStan flags it as dead code
    private function forceSqliteDbType(): void
    {
        $_ENV['DB_TYPE'] = 'sqlite';
        $_ENV['DATABASE_PATH'] = $this->getRunTempPath() . '/' . self::DB_FILENAME;
    }

    // per-process dir so concurrent suite runs in one checkout cannot clobber
    // each other's sqlite database or compiled DI container;
    // tests/phinxConfiguration.php derives the same path from getmypid()
    protected function getRunTempPath(): string
    {
        return __DIR__ . '/temp/run_' . (int)getmypid();
    }

    /**
     * @param array<string,string> $body
     * @param array<string,string> $serverParams
     * @param array<string,string> $cookies
     */
    protected function createRequest(
        string $path,
        string $method = 'GET',
        array $body = [],
        array $serverParams = [],
        array $cookies = []
    ): Request {
        $uri = new Uri('', '', 80, $path);
        $handle = fopen('php://temp', 'wb+');
        if ($handle === false) {
            throw new RuntimeException('opening php://temp failed');
        }

        $stream = (new StreamFactory())->createStreamFromResource($handle);

        $request = new Request($method, $uri, new Headers(), $cookies, $serverParams, $stream);

        if (count($body) > 0) {
            return $request->withParsedBody($body);
        }

        return $request;
    }

    protected function clearTempFolder(): void
    {
        $files = glob(__DIR__ . '/temp/*'); // skipping hidden files
        if ($files === false) {
            throw new RuntimeException('glob function fails');
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                // tolerant: a concurrent suite run may have deleted it already
                if (!@unlink($file) && file_exists($file)) {
                    throw new RuntimeException('unlink failed on ' . $file);
                }
            }
            // prune run dirs left by crashed suites; live pids mark concurrent runs
            // (Linux-only /proc check - tests always run in the Linux dev container)
            if (is_dir($file) && str_starts_with(basename($file), 'run_')) {
                $pid = (int)substr(basename($file), 4);
                // /proc pids are namespace-local (host vs container runs), so a dead-looking
                // pid may belong to a live foreign run - also require the dir to look abandoned
                // @: the dir may vanish between glob() and here in a concurrent prune
                $mtime = @filemtime($file);
                $looksAbandoned = $mtime !== false && $mtime < time() - 3600;
                if ($pid !== (int)getmypid() && !file_exists('/proc/' . $pid) && $looksAbandoned) {
                    $this->removeDirWithFiles($file);
                }
            }
        }

        // recreation happens in getTestApp, which ensures the dir on both init paths
        $runTempPath = $this->getRunTempPath();
        if (is_dir($runTempPath)) {
            $this->removeDirWithFiles($runTempPath);
        }

        // Ensure mpdf temp directory exists (required by mpdf library)
        $mpdfTempDir = __DIR__ . '/temp/mpdf/mpdf';
        if (!is_dir($mpdfTempDir)) {
            mkdir($mpdfTempDir, 0777, true);
        }
    }

    // tolerant deletion: two concurrent suites may prune the same stale dir at once;
    // scandir instead of glob because glob skips dotfiles
    protected function removeDirWithFiles(string $dir): void
    {
        $entries = @scandir($dir);
        if ($entries === false) {
            if (!is_dir($dir)) {
                return; // pruned by a concurrent suite run
            }

            throw new RuntimeException('scandir failed on ' . $dir);
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $dir . '/' . $entry;
            if (is_dir($path) && !is_link($path)) {
                $this->removeDirWithFiles($path);
                continue;
            }

            if (!@unlink($path) && file_exists($path)) {
                throw new RuntimeException('unlink failed on ' . $path);
            }
        }

        if (!@rmdir($dir) && is_dir($dir)) {
            throw new RuntimeException('rmdir failed on ' . $dir);
        }
    }

    /**
     * @template T of object
     * @param App<ContainerInterface> $app
     * @param class-string<T> $service
     * @return T
     */
    protected function getService(App $app, string $service): object
    {
        $instance = $app->getContainer()->get($service);
        self::assertInstanceOf($service, $instance);

        return $instance;
    }

    protected function getSmallTestEvent(EventRepository $eventRepository): Event
    {
        return $eventRepository->findBySlug('test-event-small')
            ?? $this->createTestEventFromDefault($eventRepository, 'test-event-small');
    }

    protected function getTestSlugEvent(EventRepository $eventRepository): Event
    {
        return $eventRepository->findBySlug('test-slug')
            ?? $this->createTestEventFromDefault($eventRepository, 'test-slug');
    }

    /**
     * Several food-stats/export tests were written against the shared dev database's
     * 'obrok37' fixture and pin its event type (EventTypeObrok::getLanguages() returns only
     * 'cs', which those tests rely on for locale-independent assertions). Clone-and-relabel
     * like the helpers above, but also flip event_type - it has no entity setter (see
     * createTestEventFromDefault) so that needs the same raw-SQL route as setEventType().
     *
     * @param App<ContainerInterface> $app
     */
    protected function getObrokTestEvent(App $app): Event
    {
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->findBySlug('obrok37');
        if ($event !== null) {
            return $event;
        }

        $newEvent = $this->createTestEventFromDefault($eventRepository, 'obrok37');
        $this->setEventType($app->getContainer(), 'obrok', 'obrok37');

        return $eventRepository->get($newEvent->id);
    }

    /**
     * Obrok-typed event dated in the future - owner ticket transfer is gated on both the event type
     * and startDay, and the seeded event started in 2021.
     *
     * @param App<ContainerInterface> $app
     */
    protected function getOwnerTransferEvent(App $app): Event
    {
        $event = $this->getObrokTestEvent($app);
        $this->mutateEventForTest($app->getContainer(), $event, [
            'startDay' => DateTimeUtils::getDateTime('+1 month'),
        ]);

        return $event;
    }

    // Event::getData() also picks up computed pseudo-properties (eventType, availableRoles,
    // logoInBase64, ...) that are derived by a getter method with no matching setter -
    // assigning those back to a new Event throws. Whitelist to the columns Event actually
    // stores, mirrored from the class's @property list (minus id, which is auto-assigned).
    private const array CLONABLE_EVENT_FIELDS = [
        'slug', 'readableName', 'webUrl', 'dataProtectionUrl', 'contactEmail', 'logoUrl',
        'accountNumber', 'iban', 'swift', 'prefixVariableSymbol', 'constantSymbol',
        'automaticPaymentPairing', 'bankSlug', 'bankApiKey', 'defaultPrice', 'currency',
        'allowPatrols', 'maximalClosedPatrolsCount', 'minimalPatrolParticipantsCount',
        'maximalPatrolParticipantsCount',
        'allowIsts', 'maximalClosedIstsCount',
        'allowGuests', 'maximalClosedGuestsCount', 'guestPrice',
        'allowOrganizingTeam', 'maximalClosedOrganizingTeamCount', 'organizingTeamPrice',
        'organizingTeamRegistrationToken',
        'allowTroops', 'maximalClosedTroopLeadersCount', 'maximalClosedTroopParticipantsCount',
        'minimalTroopParticipantsCount', 'maximalTroopParticipantsCount',
        'maximalClosedParticipantsCount',
        'startRegistration', 'startDay', 'endDay',
        'emailFrom', 'emailFromName',
        // emailBccFrom is deliberately excluded: Event's naming convention maps it to column
        // email_bcc_from, but the migration that added it actually named the column
        // email_from_bcc - a pre-existing mismatch (see Mailer.php:263) that breaks any write
        // to this property. Out of scope for the test harness; every clone leaves it null,
        // which is fine since none of these fixtures exercise bcc mail.
        'apiKeyDeals', 'apiKeyEntry', 'apiKeyVendor', 'apiKeyVendorHealth',
        'skautisAppId',
    ];

    // migrations only ever seed one event (slug test-event-slug, id 1); under the
    // shared dev postgres, extra slugs like test-event-small/test-slug were created
    // by hand once. Per-process sqlite starts from a bare migration, so clone the
    // seeded event's fields instead of requiring manual fixture setup per process.
    private function createTestEventFromDefault(EventRepository $eventRepository, string $slug): Event
    {
        $defaultEvent = $eventRepository->findBySlug('test-event-slug');
        if ($defaultEvent === null) {
            throw new RuntimeException('Seed event test-event-slug not found - did migrations run?');
        }

        $data = $defaultEvent->getData(self::CLONABLE_EVENT_FIELDS);
        $data['slug'] = $slug;
        $data['readableName'] = $slug;

        $newEvent = new Event($data);
        $eventRepository->persist($newEvent);

        // persist() does not backfill columns the entity never touched (like event_type,
        // left to its DB default) into the in-memory row - later code reading them via
        // getEventType()/getData() then hits "Missing 'event_type' column". Re-fetching
        // forces a real row read so every column is materialised.
        return $eventRepository->get($newEvent->id);
    }

    protected function resetEventToDefault(ContainerInterface $container, string $slug = 'test-event-slug'): void
    {
        $this->ensureEventExists($container, $slug);

        /** @var Connection $connection */
        $connection = $container->get(Connection::class);
        $connection->query('UPDATE event SET event_type = %s WHERE slug = %s', 'default', $slug);
    }

    protected function setEventType(
        ContainerInterface $container,
        string $eventType,
        string $slug,
    ): void {
        $this->ensureEventExists($container, $slug);

        /** @var Connection $connection */
        $connection = $container->get(Connection::class);
        $connection->query('UPDATE event SET event_type = %s WHERE slug = %s', $eventType, $slug);
    }

    // under the shared dev postgres, slugs like test-slug were long-lived fixtures created
    // by hand, so tests could freely flip their event_type before ever fetching the event.
    // Per-process sqlite starts empty, so that same call order would silently update zero
    // rows and then have getTestSlugEvent()/getSmallTestEvent() clone a fresh 'default' one
    // over it - create the row first so the UPDATE has something to land on.
    private function ensureEventExists(ContainerInterface $container, string $slug): void
    {
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        if ($eventRepository->findBySlug($slug) === null) {
            $this->createTestEventFromDefault($eventRepository, $slug);
        }
    }

    /**
     * Mutates event fields for one test and remembers the originals; tearDown() reverts them,
     * so the shared dev database keeps its state between runs.
     *
     * @param array<string, mixed> $fields field name => new value
     */
    protected function mutateEventForTest(ContainerInterface $container, Event $event, array $fields): void
    {
        if ($this->mutatedEventRestore !== null && $this->mutatedEventRestore['eventId'] !== $event->id) {
            throw new LogicException('mutateEventForTest supports only one event per test');
        }

        $originals = $this->mutatedEventRestore['originals'] ?? [];
        $currentData = $event->getData();
        foreach (array_keys($fields) as $field) {
            if (!array_key_exists($field, $originals)) {
                $originals[$field] = $currentData[$field] ?? null;
            }
        }
        $event->assign($fields);

        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        $eventRepository->persist($event);

        $this->mutatedEventRestore = [
            'container' => $container,
            'eventId' => $event->id,
            'originals' => $originals,
        ];
    }

    protected function restoreMutatedEventFields(): void
    {
        if ($this->mutatedEventRestore === null) {
            return;
        }

        /** @var EventRepository $eventRepository */
        $eventRepository = $this->mutatedEventRestore['container']->get(EventRepository::class);
        $event = $eventRepository->get($this->mutatedEventRestore['eventId']);
        $event->assign($this->mutatedEventRestore['originals']);
        $eventRepository->persist($event);
        $this->mutatedEventRestore = null;
    }

    protected function createPaidIst(ContainerInterface $container, string $firstName, string $lastName): Participant
    {
        return $this->createIst($container, $firstName, $lastName, UserStatus::Paid);
    }

    protected function createOpenIst(ContainerInterface $container, string $firstName, string $lastName): Participant
    {
        return $this->createIst($container, $firstName, $lastName, UserStatus::Open);
    }

    /**
     * Listens on the app's real event dispatcher for Symfony Mailer's post-send event, so tests can assert
     * which addresses actually got a mail without mocking the mailer itself.
     *
     * @param App<ContainerInterface> $app
     * @return ArrayObject<int, string>
     */
    protected function captureSentMessageRecipients(App $app): ArrayObject
    {
        /** @var ArrayObject<int, string> $recipients */
        $recipients = new ArrayObject();
        $dispatcher = $this->getService($app, EventDispatcherInterface::class);
        $dispatcher->addListener(SentMessageEvent::class, static function (SentMessageEvent $event) use ($recipients): void {
            foreach ($event->getMessage()->getEnvelope()->getRecipients() as $recipient) {
                $recipients[] = $recipient->getAddress();
            }
        });

        return $recipients;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    protected function initializeMailerSettings(App $app, Event $event): void
    {
        $mailerSettings = $this->getService($app, MailerSettings::class);
        $mailerSettings->setEvent($event);
        $mailerSettings->setFullUrlLink('http://test.example.com/v2/event/' . $event->slug);

        $view = $this->getService($app, Twig::class);
        $view->getEnvironment()->addGlobal('event', $event);
    }

    private function createIst(
        ContainerInterface $container,
        string $firstName,
        string $lastName,
        UserStatus $status,
    ): Participant {
        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        /** @var ParticipantRepository $participantRepository */
        $participantRepository = $container->get(ParticipantRepository::class);

        $event = $this->getTestSlugEvent($eventRepository);
        $email = 'ist-' . $status->value . '-' . bin2hex(random_bytes(6)) . '@example.com';
        $user = $userService->registerEmailUser($email, $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');
        $participant->firstName = $firstName;
        $participant->lastName = $lastName;
        $participantRepository->persist($participant);
        $user->status = $status;
        $userRepository->persist($user);

        return $participant;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    protected function createAdminUser(App $app): User
    {
        $userRepository = $this->getService($app, UserRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $testEvent = $eventRepository->get(1);

        $user = new User();
        $user->event = $testEvent;
        $user->role = UserRole::Admin;
        $user->email = 'admin@example.com';
        $user->loginType = UserLoginType::Email;
        $userRepository->persist($user);

        return $user;
    }
}
