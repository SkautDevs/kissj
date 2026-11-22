<?php

declare(strict_types=1);

namespace kissj\Programme\Image;

use GdImage;
use RuntimeException;

final class ProgrammeImageProcessor
{
    // one decoded truecolor bitmap of this size still fits into PHP-FPM's 128M memory limit
    public const int MAX_PIXELS = 20_000_000;
    public const int FULL_MAX_EDGE = 1600;
    public const int THUMB_EDGE = 256;
    public const int WEBP_FULL_QUALITY = 80;
    public const int WEBP_THUMB_QUALITY = 75;
    public const int JPEG_FULL_QUALITY = 82;
    public const int JPEG_THUMB_QUALITY = 80;

    private const string JPEG_START_OF_IMAGE = "\xFF\xD8";

    private readonly bool $webp;

    public function __construct(?bool $webp = null)
    {
        $this->webp = $webp ?? self::webpSupported();
    }

    public static function webpSupported(): bool
    {
        return (imagetypes() & IMG_WEBP) !== 0;
    }

    public function exceedsPixelCap(string $bytes): bool
    {
        $size = @getimagesizefromstring($bytes);
        if ($size === false) {
            return true;
        }

        return $size[0] * $size[1] > self::MAX_PIXELS;
    }

    /**
     * @throws UndecodableImageException
     */
    public function process(string $bytes): ProcessedImage
    {
        $orientation = $this->readExifOrientation($bytes);
        $source = $this->decode($bytes);
        $full = $this->resizeToFit($source, self::FULL_MAX_EDGE);
        $thumb = $this->cropCentredSquare($source, self::THUMB_EDGE);
        unset($source);

        return new ProcessedImage(
            $this->encode($this->orient($full, $orientation), self::WEBP_FULL_QUALITY, self::JPEG_FULL_QUALITY),
            $this->encode($this->orient($thumb, $orientation), self::WEBP_THUMB_QUALITY, self::JPEG_THUMB_QUALITY),
        );
    }

    /**
     * Also takes originals stored before this processor existed, which are raw camera JPEGs.
     *
     * @throws UndecodableImageException
     */
    public function thumbnail(string $bytes): EncodedImage
    {
        $orientation = $this->readExifOrientation($bytes);
        $source = $this->decode($bytes);
        $thumb = $this->cropCentredSquare($source, self::THUMB_EDGE);
        unset($source);

        return $this->encode($this->orient($thumb, $orientation), self::WEBP_THUMB_QUALITY, self::JPEG_THUMB_QUALITY);
    }

    private function decode(string $bytes): GdImage
    {
        $image = $bytes === '' ? false : @imagecreatefromstring($bytes);
        if ($image === false) {
            throw new UndecodableImageException('GD cannot decode the image');
        }

        return $image;
    }

    private function readExifOrientation(string $bytes): int
    {
        $isJpeg = str_starts_with($bytes, self::JPEG_START_OF_IMAGE);
        if ($isJpeg === false || function_exists('exif_read_data') === false) {
            return 1;
        }

        $stream = fopen('php://memory', 'r+b');
        if ($stream === false) {
            return 1;
        }
        fwrite($stream, $bytes);
        rewind($stream);
        $exif = @exif_read_data($stream);
        fclose($stream);

        $orientation = is_array($exif) ? ($exif['Orientation'] ?? 1) : 1;

        return is_int($orientation) ? $orientation : 1;
    }

    private function resizeToFit(GdImage $source, int $maxEdge): GdImage
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1.0, $maxEdge / max($width, $height));
        $targetWidth = max(1, (int)round($width * $scale));
        $targetHeight = max(1, (int)round($height * $scale));

        $target = $this->createCanvas($targetWidth, $targetHeight);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        return $target;
    }

    /**
     * @param int<1, max> $edge
     */
    private function cropCentredSquare(GdImage $source, int $edge): GdImage
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $side = min($width, $height);

        $target = $this->createCanvas($edge, $edge);
        imagecopyresampled(
            $target,
            $source,
            0,
            0,
            intdiv($width - $side, 2),
            intdiv($height - $side, 2),
            $edge,
            $edge,
            $side,
            $side,
        );

        return $target;
    }

    /**
     * @param int<1, max> $width
     * @param int<1, max> $height
     */
    private function createCanvas(int $width, int $height): GdImage
    {
        $canvas = imagecreatetruecolor($width, $height);
        if ($this->webp) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);

            return $canvas;
        }

        // JPEG has no alpha: transparent source pixels are blended onto white
        $white = imagecolorallocate($canvas, 255, 255, 255);
        if ($white === false) {
            throw new RuntimeException('Cannot allocate white');
        }
        imagefilledrectangle($canvas, 0, 0, $width - 1, $height - 1, $white);

        return $canvas;
    }

    /**
     * Turns only the resampled images upright: rotating the decoded source would copy the whole bitmap.
     */
    private function orient(GdImage $image, int $exifOrientation): GdImage
    {
        return match ($exifOrientation) {
            2 => $this->flip($image, IMG_FLIP_HORIZONTAL),
            3 => $this->rotateCounterClockwise($image, 180),
            4 => $this->flip($image, IMG_FLIP_VERTICAL),
            5 => $this->flip($this->rotateCounterClockwise($image, 270), IMG_FLIP_HORIZONTAL),
            6 => $this->rotateCounterClockwise($image, 270),
            7 => $this->flip($this->rotateCounterClockwise($image, 90), IMG_FLIP_HORIZONTAL),
            8 => $this->rotateCounterClockwise($image, 90),
            default => $image,
        };
    }

    private function rotateCounterClockwise(GdImage $image, int $degrees): GdImage
    {
        $rotated = imagerotate($image, $degrees, 0);
        if ($rotated === false) {
            throw new RuntimeException('Cannot rotate the image');
        }

        return $rotated;
    }

    private function flip(GdImage $image, int $mode): GdImage
    {
        imageflip($image, $mode);

        return $image;
    }

    private function encode(GdImage $image, int $webpQuality, int $jpegQuality): EncodedImage
    {
        ob_start();
        if ($this->webp) {
            imagesavealpha($image, true);
            $written = imagewebp($image, null, $webpQuality);
        } else {
            $written = imagejpeg($image, null, $jpegQuality);
        }
        $bytes = ob_get_clean();
        if ($written === false || $bytes === false || $bytes === '') {
            throw new RuntimeException('Cannot encode the image');
        }

        return new EncodedImage($bytes, $this->webp ? 'image/webp' : 'image/jpeg');
    }
}
