<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\User\User;
use kissj\User\UserLoginType;
use kissj\User\UserRepository;
use kissj\User\UserService;
use LeanMapper\Connection;
use RuntimeException;
use Tests\AppTestCase;

class DatabaseIsolationTest extends AppTestCase
{
    public function testForeignKeysAreEnforced(): void
    {
        $app = $this->getTestApp();
        $connection = $this->getService($app, Connection::class);

        /** @var string $foreignKeysPragma */
        $foreignKeysPragma = $connection->query('PRAGMA foreign_keys')->fetchSingle();
        self::assertSame(1, (int)$foreignKeysPragma);
    }

    public function testEachFreshInitStartsFromCleanDatabase(): void
    {
        $app = $this->getTestApp();
        $userRepository = $this->getService($app, UserRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->get(1);

        $user = new User();
        $user->event = $event;
        $user->email = 'isolation-probe@example.com';
        $user->loginType = UserLoginType::Email;
        $userRepository->persist($user);

        self::assertTrue($userRepository->isEmailUserExisting('isolation-probe@example.com', $event));

        $secondApp = $this->getTestApp();
        $secondUserRepository = $this->getService($secondApp, UserRepository::class);
        $secondEventRepository = $this->getService($secondApp, EventRepository::class);
        $secondEvent = $secondEventRepository->get(1);

        self::assertFalse(
            $secondUserRepository->isEmailUserExisting('isolation-probe@example.com', $secondEvent),
        );
    }

    public function testFreshInitClonesDatabaseFromProcessTemplate(): void
    {
        $app = $this->getTestApp();
        $userService = $this->getService($app, UserService::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $userService->registerEmailUser('template-marker@example.com', $eventRepository->get(1));

        $runDir = $this->getRunTempPath();
        $templatePath = $runDir . '/' . self::DB_TEMPLATE_FILENAME;
        self::assertFileExists($templatePath);

        // doctor the template with the marker row: the next fresh init must serve its
        // content verbatim, proving the clone path (real templates are clean snapshots)
        if (!copy($runDir . '/' . self::DB_FILENAME, $templatePath)) {
            throw new RuntimeException('doctoring the template failed');
        }

        $secondApp = $this->getTestApp();
        self::assertTrue(
            $this->getService($secondApp, UserRepository::class)->isEmailUserExisting(
                'template-marker@example.com',
                $this->getService($secondApp, EventRepository::class)->get(1),
            ),
        );

        // template gone mid-process -> re-migrate clean + re-snapshot
        unlink($templatePath);
        $thirdApp = $this->getTestApp();
        self::assertFalse(
            $this->getService($thirdApp, UserRepository::class)->isEmailUserExisting(
                'template-marker@example.com',
                $this->getService($thirdApp, EventRepository::class)->get(1),
            ),
        );
        self::assertFileExists($templatePath);
    }

    public function testConcurrentRunTempDirsSurviveTempCleanup(): void
    {
        // a real child process simulates a live concurrent test run - its pid is unique
        // per suite run, so two suites executing this test cannot clobber each other's
        // fixture; a fixed pid (e.g. 1) would be shared cross-process state
        $child = proc_open(['sleep', '30'], [], $pipes);
        if ($child === false) {
            throw new RuntimeException('spawning child process failed');
        }
        $childPid = proc_get_status($child)['pid'];

        // above kernel pid_max, offset by own pid to stay unique across concurrent suites
        $stalePid = 999_000_000 + (int)getmypid();
        $liveDir = __DIR__ . '/../temp/run_' . $childPid;
        $staleDir = __DIR__ . '/../temp/run_' . $stalePid;
        if (!is_dir($liveDir)) {
            mkdir($liveDir, 0777, true);
        }
        if (!is_dir($staleDir)) {
            mkdir($staleDir, 0777, true);
        }
        touch($liveDir . '/' . self::DB_FILENAME);
        touch($staleDir . '/' . self::DB_FILENAME);
        // pruning requires the dir to look abandoned, not just a dead pid
        touch($staleDir, time() - 7200);
        // backdate the live dir too, so surviving depends on the pid-liveness check alone
        touch($liveDir, time() - 7200);

        try {
            // direct call: getTestApp prunes only on the process's first fresh init,
            // which another test has usually consumed already
            $this->clearTempFolder();

            self::assertFileExists($liveDir . '/' . self::DB_FILENAME);
            self::assertDirectoryDoesNotExist($staleDir);
        } finally {
            proc_terminate($child);
            proc_close($child);
            $this->removeDirWithFiles($liveDir);
            $this->removeDirWithFiles($staleDir);
        }
    }
}
