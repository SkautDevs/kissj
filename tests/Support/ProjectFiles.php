<?php

declare(strict_types=1);

namespace Tests\Support;

use FilesystemIterator;
use LogicException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class ProjectFiles
{
    public const string ROOT = __DIR__ . '/../..';

    /**
     * @return list<string>
     */
    public static function withExtension(string $relativeDirectory, string $extension): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(self::ROOT . '/' . $relativeDirectory, FilesystemIterator::SKIP_DOTS),
        );

        $paths = [];
        foreach ($iterator as $file) {
            if ($file instanceof SplFileInfo === false || $file->getExtension() !== $extension) {
                continue;
            }

            $realPath = $file->getRealPath();
            if ($realPath === false) {
                throw new LogicException('Cannot resolve path ' . $file->getPathname());
            }

            $paths[] = $realPath;
        }

        sort($paths);

        return $paths;
    }
}
