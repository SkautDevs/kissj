<?php declare(strict_types=1);

namespace Tests;

use kissj\Application\ApplicationGetter;
use kissj\Event\EventRepository;
use kissj\User\User;
use kissj\User\UserRepository;
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
    protected function getTestApp(bool $freshInit = true): App
    {
        if ($freshInit) {
            $this->clearTempFolder();

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
     * @param string $path
     * @param string $method
     * @param array<string,string> $body
     * @param array<string,string> $serverParams
     * @param array<string,string> $cookies
     * @return Request
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
        $user->role = User::ROLE_ADMIN;
        $user->email = 'admin@example.com';
        $userRepository->persist($user);
        
        return $user;
    }
}
