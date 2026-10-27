<?php

declare(strict_types=1);

namespace Tests\Unit\CodeStyle;

use LogicException;
use PHPUnit\Framework\TestCase;
use Tests\Support\ProjectFiles;

class NoEmDashTest extends TestCase
{
    private const string EM_DASH = "\u{2014}";

    public function testNoPhpLineContainsEmDash(): void
    {
        $paths = [
            ...ProjectFiles::withExtension('src', 'php'),
            ...ProjectFiles::withExtension('tests', 'php'),
        ];

        $hits = [];
        foreach ($paths as $path) {
            if (str_contains($path, '/tests/temp/')) {
                continue;
            }

            $lines = file($path);
            if ($lines === false) {
                throw new LogicException('Cannot read ' . $path);
            }

            foreach ($lines as $index => $line) {
                if (str_contains($line, self::EM_DASH)) {
                    $hits[] = $path . ':' . ($index + 1);
                }
            }
        }

        self::assertSame([], $hits, 'replace each em-dash with a colon, parentheses, a comma or a new sentence');
    }
}
