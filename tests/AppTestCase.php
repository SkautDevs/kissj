<?php

declare(strict_types=1);

namespace Tests;

use kissj\Application\ApplicationGetter;
use kissj\Event\EventRepository;
use kissj\User\User;
use kissj\User\UserLoginType;
use kissj\User\UserRepository;
use kissj\User\UserRole;
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
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

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
