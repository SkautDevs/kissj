<?php

declare(strict_types=1);

namespace Tests\Functional;

use Tests\AppTestCase;

class ApiTest extends AppTestCase
{
    private const TEST_EVENT_PREFIX_URL = '/v3/event/test-event-slug/';

    /**
     * @dataProvider provideRoutes
     */
    public function testRoutes(
        string $method,
        string $route,
        string $json,
        int $expectedStatsCode
    ): void {
        /** @var array<string,string> $parsedBody */
        $parsedBody = json_decode($json, true, 512, flags: JSON_THROW_ON_ERROR);
        $request = $this->createRequest(
            $route,
            $method,
            $parsedBody,
        );
        $response = $this->getTestApp()->handle($request);
        $this->markTestSkipped('login is needed to implement to tests');

        $this->assertEquals($expectedStatsCode, $response->getStatusCode());
    }

    /**
     * @return array<string, array<int, int|string>>
     */
    public function provideRoutes(): array
    {
        return [
            'change admin note' => [
                'POST',
                self::TEST_EVENT_PREFIX_URL . 'admin/1/adminNote',
                '{"note":"testing admin notes"}',
                201,
            ],
        ];
    }
}
