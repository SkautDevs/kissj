<?php

declare(strict_types=1);

namespace Tests\Support;

use GdImage;
use LogicException;

class ImageFixtures
{
    public static function png(int $width, int $height): string
    {
        $image = self::createSolid($width, $height, 40, 120, 200);

        return self::encode(static fn (): bool => imagepng($image));
    }

    public static function jpeg(int $width, int $height): string
    {
        $image = self::createSolid($width, $height, 40, 120, 200);

        return self::encode(static fn (): bool => imagejpeg($image, null, 90));
    }

    public static function stripedPortraitPng(int $width, int $height): string
    {
        $image = self::createSolid($width, $height, 0, 0, 0);
        $third = intdiv($height, 3);
        self::fillRectangle($image, 0, 0, $width - 1, $third - 1, 255, 0, 0);
        self::fillRectangle($image, 0, $third, $width - 1, 2 * $third - 1, 0, 255, 0);
        self::fillRectangle($image, 0, 2 * $third, $width - 1, $height - 1, 0, 0, 255);

        return self::encode(static fn (): bool => imagepng($image));
    }

    /**
     * A 600 x 300 JPEG with an Exif Orientation tag, stored the way a camera stores it: turned or mirrored
     * so that, once the orientation is applied, it shows red top-left, green top-right, blue bottom-left
     * and white bottom-right quadrants.
     */
    public static function quadrantJpegWithOrientation(int $orientation): string
    {
        // the upright quadrant (TL, TR, BL, BR) that each stored quadrant shows up in
        $uprightOfStored = match ($orientation) {
            1 => ['TL', 'TR', 'BL', 'BR'],
            2 => ['TR', 'TL', 'BR', 'BL'],
            3 => ['BR', 'BL', 'TR', 'TL'],
            4 => ['BL', 'BR', 'TL', 'TR'],
            5 => ['TL', 'BL', 'TR', 'BR'],
            6 => ['TR', 'BR', 'TL', 'BL'],
            7 => ['BR', 'TR', 'BL', 'TL'],
            8 => ['BL', 'TL', 'BR', 'TR'],
            default => throw new LogicException('Unknown Exif orientation ' . $orientation),
        };
        $uprightColours = ['TL' => [255, 0, 0], 'TR' => [0, 255, 0], 'BL' => [0, 0, 255], 'BR' => [255, 255, 255]];
        $storedCorners = [[0, 0, 299, 149], [300, 0, 599, 149], [0, 150, 299, 299], [300, 150, 599, 299]];

        $image = self::createSolid(600, 300, 0, 0, 0);
        foreach ($storedCorners as $index => [$x1, $y1, $x2, $y2]) {
            [$red, $green, $blue] = $uprightColours[$uprightOfStored[$index]];
            self::fillRectangle($image, $x1, $y1, $x2, $y2, $red, $green, $blue);
        }
        $jpeg = self::encode(static fn (): bool => imagejpeg($image, null, 95));

        $tiff = 'MM' . pack('nN', 42, 8)
            . pack('n', 1)
            . pack('nnNnn', 0x0112, 3, 1, $orientation, 0)
            . pack('N', 0);
        $app1Payload = "Exif\x00\x00" . $tiff;
        $app1 = "\xFF\xE1" . pack('n', strlen($app1Payload) + 2) . $app1Payload;

        // the Exif segment goes right after the SOI marker, where cameras put it
        return substr($jpeg, 0, 2) . $app1 . substr($jpeg, 2);
    }

    public static function pngHeaderOnly(int $width, int $height): string
    {
        $ihdr = 'IHDR' . pack('NNCCCCC', $width, $height, 8, 2, 0, 0, 0);

        return "\x89PNG\r\n\x1a\n" . pack('N', 13) . $ihdr . pack('N', crc32($ihdr));
    }

    private static function createSolid(int $width, int $height, int $red, int $green, int $blue): GdImage
    {
        if ($width < 1 || $height < 1) {
            throw new LogicException('Image edges must be positive');
        }

        $image = imagecreatetruecolor($width, $height);
        self::fillRectangle($image, 0, 0, $width - 1, $height - 1, $red, $green, $blue);

        return $image;
    }

    private static function fillRectangle(
        GdImage $image,
        int $x1,
        int $y1,
        int $x2,
        int $y2,
        int $red,
        int $green,
        int $blue,
    ): void {
        $colour = imagecolorallocate($image, max(0, min(255, $red)), max(0, min(255, $green)), max(0, min(255, $blue)));
        if ($colour === false) {
            throw new LogicException('Cannot allocate colour');
        }

        imagefilledrectangle($image, $x1, $y1, $x2, $y2, $colour);
    }

    /**
     * @param callable(): bool $writer
     */
    private static function encode(callable $writer): string
    {
        ob_start();
        $written = $writer();
        $bytes = ob_get_clean();
        if ($written === false || $bytes === false) {
            throw new LogicException('Cannot encode image fixture');
        }

        return $bytes;
    }
}
