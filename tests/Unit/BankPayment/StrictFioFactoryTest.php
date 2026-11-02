<?php

declare(strict_types=1);

namespace Tests\Unit\BankPayment;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use h4kuna\Fio\Exceptions\ServiceUnavailable;
use kissj\BankPayment\StrictFioFactory;
use PHPUnit\Framework\TestCase;

class StrictFioFactoryTest extends TestCase
{
    public function testLastDownloadWithEmptyBodyThrowsServiceUnavailable(): void
    {
        $fioFactory = new StrictFioFactory(
            ['test' => ['account' => '2600000000/2010', 'token' => bin2hex(random_bytes(20))]],
            sys_get_temp_dir() . '/kissj-fio-test',
            new Client(['handler' => HandlerStack::create(new MockHandler([new Response(200, [], '')]))]),
        );

        $this->expectException(ServiceUnavailable::class);

        $fioFactory->createFioRead('test')->lastDownload();
    }
}
