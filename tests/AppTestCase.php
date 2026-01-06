<?php declare(strict_types=1);

namespace Tests;

use kissj\Application\ApplicationGetter;
use kissj\Event\EventRepository;
use kissj\User\User;
use kissj\User\UserLoginType;
use kissj\User\UserRepository;
use kissj\User\UserRole;
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
    
    protected function createAdminUser(App $app): User
    {
        /** @var ContainerInterface $container */
        $container = $app->getContainer();

        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
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
