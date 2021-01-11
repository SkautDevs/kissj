<?php

namespace Tests;

use kissj\Application\ApplicationGetter;
use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request;
use Slim\Psr7\Uri;

class AppTestCase extends TestCase {
    protected function getTestApp(bool $freshInit = true): App {
        $testDbFullPath = __DIR__.'/temp/db_tests.sqlite';
        if ($freshInit) {
            $this->clearTempFolder();
            $pdo = new \PDO('sqlite:'.$testDbFullPath);
            $sqlInit = file_get_contents(__DIR__.'/../sql/init.sql');
            if ($sqlInit === false) {
                throw new \RuntimeException('loading of sql/init.sql file failed');
            }
            $pdo->exec($sqlInit);
        }

        return (new ApplicationGetter())->getApp(
            __DIR__.'/', 
            'env.testing', 
            $testDbFullPath, 
            __DIR__.'/temp'
        );
    }

    protected function createRequest(
        string $path,
        string $method = 'GET',
        array $body = [],
        array $serverParams = [],
        array $cookies = []
    ): Request {
        $uri = new Uri('', '', 80, $path);
        $handle = fopen('php://temp', 'wb+');
        $stream = (new StreamFactory())->createStreamFromResource($handle);

        $headers = new Headers();
        foreach ($headers as $name => $value) {
            $headers->addHeader($name, $value);
        }

        $request = new Request($method, $uri, $headers, $cookies, $serverParams, $stream);

        if (count($body) > 0) {
            return $request->withParsedBody($body);
        }

        return $request;
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
