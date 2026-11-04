<?php

declare(strict_types=1);

namespace Tests\Functional;

use ErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tests\AppTestCase;

class RequestErrorReportingTest extends AppTestCase
{
    public function testWarningRaisedInsideRequestFailsTheRequest(): void
    {
        $app = $this->getTestApp();
        $app->get('/__test/warning', function (ServerRequestInterface $request, ResponseInterface $response) {
            trigger_error('request-time warning', E_USER_WARNING);

            return $response;
        });

        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('request-time warning');

        $app->handle($this->createRequest('/__test/warning'));
    }

    public function testDeprecationRaisedInsideRequestFailsTheRequest(): void
    {
        $app = $this->getTestApp();
        $app->get('/__test/deprecation', function (ServerRequestInterface $request, ResponseInterface $response) {
            trigger_error('request-time deprecation', E_USER_DEPRECATED);

            return $response;
        });

        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('request-time deprecation');

        $app->handle($this->createRequest('/__test/deprecation'));
    }
}
