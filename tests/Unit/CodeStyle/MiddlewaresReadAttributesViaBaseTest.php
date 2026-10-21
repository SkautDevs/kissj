<?php

declare(strict_types=1);

namespace Tests\Unit\CodeStyle;

use LogicException;
use PHPUnit\Framework\TestCase;
use Tests\Support\ProjectFiles;

class MiddlewaresReadAttributesViaBaseTest extends TestCase
{
    public function testOnlyBaseMiddlewareReadsUserAndEventAttributes(): void
    {
        $hits = [];
        foreach (ProjectFiles::withExtension('src/Middleware', 'php') as $path) {
            if (str_ends_with($path, '/BaseMiddleware.php')) {
                continue;
            }

            $lines = file($path);
            if ($lines === false) {
                throw new LogicException('Cannot read ' . $path);
            }

            foreach ($lines as $index => $line) {
                if (str_contains($line, "getAttribute('user')") || str_contains($line, "getAttribute('event')")) {
                    $hits[] = $path . ':' . ($index + 1);
                }
            }
        }

        self::assertSame([], $hits, 'use BaseMiddleware::tryGetUser() and tryGetEvent() instead of getAttribute()');
    }
}
