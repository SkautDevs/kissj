<?php

namespace Tests;

use DI\Bridge\Slim\Bridge;
use DI\ContainerBuilder;
use kissj\Settings\Settings;
use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request;
use Slim\Psr7\Uri;

class AppTestCase extends TestCase {
    protected function getTestApp(): App {
        $this->clearTempFolder();

        $testDbFullPath = __DIR__.'/temp/db_tests.sqlite';

        $pdo = new \PDO('sqlite:'.$testDbFullPath);
        $pdo->exec(file_get_contents(__DIR__.'/../sql/init.sql'));

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions((new Settings())->getContainerDefinition(
            __DIR__.'/',
            'env.testing',
            $testDbFullPath
        ));
        $containerBuilder->useAnnotations(true); // used in AbstractController
        if ($_ENV['DEBUG'] === 'false') {
            // TODO add autowired definitions into container to get more performace
            // https://php-di.org/doc/performances.html#optimizing-for-compilation
            $containerBuilder->enableCompilation(__DIR__.'/temp');
        }
        $app = Bridge::create($containerBuilder->build());

        require __DIR__.'/../src/middleware.php';
        require __DIR__.'/../src/routes.php';

        return $app;
    }

    protected function createRequest(
        string $path,
        string $method = 'GET',
        array $cookies = [],
        array $serverParams = []
    ): Request {
        $uri = new Uri('', '', 80, $path);
        $handle = fopen('php://temp', 'wb+');
        $stream = (new StreamFactory())->createStreamFromResource($handle);

        $headers = new Headers();
        foreach ($headers as $name => $value) {
            $headers->addHeader($name, $value);
        }

        return new Request($method, $uri, $headers, $cookies, $serverParams, $stream);
    }

    protected function clearTempFolder(): void {
        $files = glob(__DIR__.'/temp/*'); // skipping hidden files
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
