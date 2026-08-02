<?php

declare(strict_types=1);

namespace Tests;

use kissj\Application\ApplicationGetter;
use kissj\Event\EventRepository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\User\User;
use kissj\User\UserLoginType;
use kissj\User\UserRepository;
use kissj\User\UserRole;
use kissj\User\UserService;
use kissj\User\UserStatus;
use LeanMapper\Connection;
use Phinx\Console\PhinxApplication;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request;
use Slim\Psr7\Uri;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class AppTestCase extends TestCase
{
    /** @var callable|null */
    private $originalErrorHandler = null;

    /** @var callable|null */
    private $originalExceptionHandler = null;

    // getTestApp() builds a brand-new DI container (and thus a new pg_connect()) every call;
    // nothing else ever closes them, so across the whole suite that exhausts Postgres's
    // max_connections. Track every connection a test creates and close it in tearDown().
    /** @var Connection[] */
    private array $connectionsToClose = [];

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
        foreach ($this->connectionsToClose as $connection) {
            if ($connection->isConnected()) {
                $connection->disconnect();
            }
        }
        $this->connectionsToClose = [];

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
        if ($freshInit) {
            $this->clearTempFolder();

            // Properly destroy any active session before starting fresh
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_unset();
                session_destroy();
            }

            // Clear session superglobal
            $_SESSION = [];

            $arguments = [
                'command' => 'migrate',
                '--configuration' => __DIR__ . '/phinxConfiguration.php',
            ];

            $phinx = new PhinxApplication();
            $phinx->setAutoExit(false);
            $phinx->run(new ArrayInput($arguments), new BufferedOutput());
        }

        $app = (new ApplicationGetter())->getApp(
            __DIR__ . '/',
            'env.testing',
            __DIR__ . '/temp'
        );

        $container = $app->getContainer();
        if ($container !== null) {
            /** @var Connection $connection */
            $connection = $container->get(Connection::class);
            $this->connectionsToClose[] = $connection;
        }

        return $app;
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
            throw new \RuntimeException('opening php://temp failed');
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
            throw new \RuntimeException('glob function fails');
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        // Ensure mpdf temp directory exists (required by mpdf library)
        $mpdfTempDir = __DIR__ . '/temp/mpdf/mpdf';
        if (!is_dir($mpdfTempDir)) {
            mkdir($mpdfTempDir, 0777, true);
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

    protected function resetEventToDefault(ContainerInterface $container, string $slug = 'test-event-slug'): void
    {
        /** @var Connection $connection */
        $connection = $container->get(Connection::class);
        $connection->query('UPDATE event SET event_type = %s WHERE slug = %s', 'default', $slug);
    }

    protected function setEventType(
        ContainerInterface $container,
        string $eventType,
        string $slug,
    ): void {
        /** @var Connection $connection */
        $connection = $container->get(Connection::class);
        $connection->query('UPDATE event SET event_type = %s WHERE slug = %s', $eventType, $slug);
    }

    protected function createPaidIst(ContainerInterface $container, string $firstName, string $lastName): Participant
    {
        return $this->createIst($container, $firstName, $lastName, UserStatus::Paid);
    }

    protected function createOpenIst(ContainerInterface $container, string $firstName, string $lastName): Participant
    {
        return $this->createIst($container, $firstName, $lastName, UserStatus::Open);
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

        $event = $eventRepository->get(4);
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
