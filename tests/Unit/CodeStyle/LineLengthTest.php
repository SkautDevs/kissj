<?php

declare(strict_types=1);

namespace Tests\Unit\CodeStyle;

use LogicException;
use PHPUnit\Framework\TestCase;
use Tests\Support\ProjectFiles;

class LineLengthTest extends TestCase
{
    private const int MAX_LINE_LENGTH = 120;

    public function testNoPhpLineExceedsMaximumLength(): void
    {
        $paths = [
            ...ProjectFiles::withExtension('src', 'php'),
            ...ProjectFiles::withExtension('tests', 'php'),
        ];

        $hits = [];
        foreach ($paths as $path) {
            if (
                str_contains($path, '/tests/temp/')
                || str_contains($path, '/src/Application/migrations/seeds/programmes/')
            ) {
                continue;
            }

            $lines = file($path);
            if ($lines === false) {
                throw new LogicException('Cannot read ' . $path);
            }

            foreach ($lines as $index => $line) {
                $length = mb_strlen(str_replace("\t", '    ', rtrim($line, "\r\n")));
                if ($length > self::MAX_LINE_LENGTH) {
                    $hits[] = $path . ':' . ($index + 1) . ' (' . $length . ' chars)';
                }
            }
        }

        self::assertSame([], $hits, 'break long lines: one argument per line, split conditions, concatenate strings');
    }
}
