<?php

declare(strict_types=1);

namespace Tests\Functional;

use Tests\AppTestCase;

class ApiTest extends AppTestCase
{
    private const TEST_EVENT_PREFIX_URL = '/v3/event/test-event-slug';
    private const TEST_PREFIX_URL = '/v3';

    /**
     * @dataProvider provideRoutes
     */
    public function testRoutesWithoutAuth(
        string $method,
        string $route,
        string $json,
        int $expectedStatsCode,
        string $expectedJson,
    ): void {
        /** @var array<string,string> $parsedBody */
        $parsedBody = json_decode($json, true, 512, flags: JSON_THROW_ON_ERROR);
        $request = $this->createRequest(
            $route,
            $method,
            $parsedBody,
        );
        $response = $this->getTestApp()->handle($request);

        $this->markTestSkipped('need to introduce database seeds');
        $this->assertEquals($expectedJson, (string)$response->getBody());
        $this->assertEquals($expectedStatsCode, $response->getStatusCode());
    }

    /**
     * @return array<string, array<int, int|string>>
     */
    public function provideRoutes(): array
    {
        $validEntryCode = 'validEntryCode';
        $eventSecret = 'validSecret';
        
        return [
            'invalid entry code' => [
                'POST',
                self::TEST_PREFIX_URL . '/entry/randomEntryCode',
                sprintf('{"eventSecret": "%s"}', $eventSecret),
                403,
                '{"status":"unvalid","reason":"participant not found"}'
            ],
            'invalid event secret' => [
                'POST',
                self::TEST_PREFIX_URL . '/entry/randomEntryCode',
                '{"eventSecret": "randomSecret"}',
                403,
                '{"status":"unvalid","reason":"unvalid event secret"}'
            ],
            'first enter' => [
                'POST',
                self::TEST_PREFIX_URL . '/entry/' . $validEntryCode,
                sprintf('{"eventSecret": "%s"}', $eventSecret),
                200,
                '{}'
            ],
            'second enter' => [
                'POST',
                self::TEST_PREFIX_URL . '/entry/' . $validEntryCode,
                sprintf('{"eventSecret": "%s"}', $eventSecret),
                200,
                '{}'
            ],
            /*'change admin note' => [
                'POST',
                self::TEST_EVENT_PREFIX_URL . 'admin/1/adminNote',
                '{"note":"testing admin notes"}',
                201,
            ],*/
        ];
    }
}
