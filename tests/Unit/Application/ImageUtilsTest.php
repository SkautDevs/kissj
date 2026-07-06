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

    public function testRealRasterImagesAreAccepted(): void
    {
        self::assertTrue(ImageUtils::isSupportedRasterImage($this->rasterBytes('png')));
        self::assertTrue(ImageUtils::isSupportedRasterImage($this->rasterBytes('gif')));
        self::assertTrue(ImageUtils::isSupportedRasterImage($this->rasterBytes('jpeg')));
    }

    public function testSpoofedAndNonRasterPayloadsAreRejected(): void
    {
        self::assertFalse(ImageUtils::isSupportedRasterImage(''));
        self::assertFalse(ImageUtils::isSupportedRasterImage('not an image at all'));
        self::assertFalse(ImageUtils::isSupportedRasterImage(
            '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>',
        ));
        self::assertFalse(ImageUtils::isSupportedRasterImage('<html><body><script>alert(1)</script></body></html>'));
    }

    private function rasterBytes(string $format): string
    {
        $image = imagecreatetruecolor(1, 1);
        if ($image === false) {
            self::fail('GD could not create a test image');
        }

        ob_start();
        $encoded = match ($format) {
            'png' => imagepng($image),
            'gif' => imagegif($image),
            'jpeg' => imagejpeg($image),
            default => self::fail('unsupported test image format: ' . $format),
        };
        $bytes = (string)ob_get_clean();
        imagedestroy($image);
        self::assertTrue($encoded);

        return $bytes;
    }
}
