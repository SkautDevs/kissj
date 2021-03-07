<?php

namespace Tests;

use kissj\Application\ApplicationGetter;
use Phinx\Console\PhinxApplication;
use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request;
use Slim\Psr7\Uri;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class AppTestCase extends TestCase {
    protected function getTestApp(bool $freshInit = true): App {
        $testDbFullPath = __DIR__.'/temp/db_tests.sqlite';
        if ($freshInit) {
            $this->clearTempFolder();
            
            if (true) { 
                // use for tradiconal 
                $pdo = new \PDO('sqlite:'.$testDbFullPath);
                $sqlInit = file_get_contents(__DIR__.'/../sql/init.sql');
                if ($sqlInit === false) {
                    throw new \RuntimeException('loading of sql/init.sql file failed');
                }
                $pdo->exec($sqlInit);
            } else {
                // TODO use migrations to tests
                $arguments = [
                    'command' => 'migrate',
                    '--configuration' => '',
                ];
                
                $phinx = new PhinxApplication();
                $phinx->find('migrate')->run(new ArrayInput($arguments), new BufferedOutput());
            }
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
