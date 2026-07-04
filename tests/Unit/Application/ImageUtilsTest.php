<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use kissj\Application\ImageUtils;
use PHPUnit\Framework\TestCase;

class ImageUtilsTest extends TestCase
{
    public function testEmptyPathReturnsEmptyStringWithoutWarning(): void
    {
        // PHPUnit does not fail on E_WARNING here, so capture them explicitly -
        // without the guard, file_get_contents() on the public/ directory warns
        $warnings = [];
        set_error_handler(static function (int $errno, string $errstr) use (&$warnings): bool {
            $warnings[] = $errstr;

            return true;
        }, E_WARNING | E_NOTICE);

        try {
            $result = ImageUtils::getLocalImageInBase64('');
        } finally {
            restore_error_handler();
        }

        self::assertSame('', $result);
        self::assertSame([], $warnings);
    }
}
