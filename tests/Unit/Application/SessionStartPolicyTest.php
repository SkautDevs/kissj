<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use kissj\Application\SessionStartPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SessionStartPolicyTest extends TestCase
{
    #[DataProvider('uris')]
    public function testOnlyImageRoutesAreSessionless(string $uri, bool $expected): void
    {
        self::assertSame($expected, SessionStartPolicy::isSessionless($uri));
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function uris(): array
    {
        $filename = str_repeat('a1', 16);

        return [
            'full image' => ['/v3/programme/image/' . $filename, true],
            'thumb' => ['/v3/programme/image/' . $filename . '/thumb', true],
            'with query' => ['/v3/programme/image/' . $filename . '?v=1', true],
            'behind a base path' => ['/kissj/v3/programme/image/' . $filename, true],
            'programme list' => ['/v3/programme/list', false],
            'short filename' => ['/v3/programme/image/' . str_repeat('a', 31), false],
            'uppercase filename' => ['/v3/programme/image/' . str_repeat('A1', 16), false],
            'admin image' => ['/v2/event/x/admin/programmes/5/image', false],
            'empty' => ['', false],
            'below thumb' => ['/v3/programme/image/' . $filename . '/thumb/x', false],
        ];
    }
}
