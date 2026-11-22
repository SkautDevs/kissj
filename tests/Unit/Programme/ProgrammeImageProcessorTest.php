<?php

declare(strict_types=1);

namespace Tests\Unit\Programme;

use GdImage;
use kissj\Programme\Image\EncodedImage;
use kissj\Programme\Image\ProgrammeImageProcessor;
use kissj\Programme\Image\UndecodableImageException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Support\ImageFixtures;

class ProgrammeImageProcessorTest extends TestCase
{
    public function testGdCanEncodeWebp(): void
    {
        self::assertTrue(ProgrammeImageProcessor::webpSupported(), 'GD without WebP would silently serve JPEG');
    }

    public function testFullIsWebpWithLongestEdge1600AndThumbIs256Square(): void
    {
        $processed = (new ProgrammeImageProcessor())->process(ImageFixtures::png(3000, 2000));

        self::assertSame('image/webp', $processed->full->contentType);
        self::assertSame([1600, 1067, IMAGETYPE_WEBP], $this->sizeAndType($processed->full));
        self::assertSame('image/webp', $processed->thumb->contentType);
        self::assertSame([256, 256, IMAGETYPE_WEBP], $this->sizeAndType($processed->thumb));
    }

    public function testSmallSourceIsNotUpscaled(): void
    {
        $processed = (new ProgrammeImageProcessor())->process(ImageFixtures::jpeg(800, 600));

        self::assertSame([800, 600, IMAGETYPE_WEBP], $this->sizeAndType($processed->full));
    }

    public function testPortraitThumbIsCoverCroppedFromTheCentre(): void
    {
        $processed = (new ProgrammeImageProcessor())->process(ImageFixtures::stripedPortraitPng(900, 2700));

        $thumb = $this->decode($processed->thumb);
        self::assertSame('green', $this->colourAt($thumb, 5, 5));
        self::assertSame('green', $this->colourAt($thumb, 250, 250));
    }

    public function testJpegFallbackWhenWebpIsOff(): void
    {
        $processed = (new ProgrammeImageProcessor(false))->process(ImageFixtures::png(2000, 1000));

        self::assertSame('image/jpeg', $processed->full->contentType);
        self::assertSame([1600, 800, IMAGETYPE_JPEG], $this->sizeAndType($processed->full));
        self::assertSame('image/jpeg', $processed->thumb->contentType);
        self::assertSame([256, 256, IMAGETYPE_JPEG], $this->sizeAndType($processed->thumb));
    }

    public function testJpegFallbackPaintsTransparentPixelsWhite(): void
    {
        $source = imagecreatetruecolor(40, 40);
        imagealphablending($source, false);
        imagesavealpha($source, true);
        $transparent = imagecolorallocatealpha($source, 0, 0, 0, 127);
        self::assertNotFalse($transparent);
        imagefilledrectangle($source, 0, 0, 39, 39, $transparent);
        ob_start();
        imagepng($source);
        $png = (string)ob_get_clean();

        $processed = (new ProgrammeImageProcessor(false))->process($png);

        self::assertSame('white', $this->colourAt($this->decode($processed->full), 20, 20));
    }

    public function testWebpKeepsTransparency(): void
    {
        $source = imagecreatetruecolor(40, 40);
        imagealphablending($source, false);
        imagesavealpha($source, true);
        $transparent = imagecolorallocatealpha($source, 0, 0, 0, 127);
        self::assertNotFalse($transparent);
        imagefilledrectangle($source, 0, 0, 39, 39, $transparent);
        ob_start();
        imagepng($source);
        $png = (string)ob_get_clean();

        $full = $this->decode((new ProgrammeImageProcessor(true))->process($png)->full);

        self::assertSame(127, (imagecolorat($full, 20, 20) >> 24) & 0x7F);
    }

    public function testThumbnailOfAStoredFull(): void
    {
        $thumb = (new ProgrammeImageProcessor())->thumbnail(ImageFixtures::png(1600, 1067));

        self::assertSame('image/webp', $thumb->contentType);
        self::assertSame([256, 256, IMAGETYPE_WEBP], $this->sizeAndType($thumb));
    }

    public function testPixelCapReadsTheHeaderOnly(): void
    {
        $processor = new ProgrammeImageProcessor();

        self::assertTrue($processor->exceedsPixelCap(ImageFixtures::pngHeaderOnly(5000, 4001)));
        self::assertFalse($processor->exceedsPixelCap(ImageFixtures::pngHeaderOnly(5000, 4000)));
        self::assertTrue($processor->exceedsPixelCap('not an image'));
    }

    public function testProcessRejectsAnImageThatDoesNotDecode(): void
    {
        $this->expectException(UndecodableImageException::class);

        (new ProgrammeImageProcessor())->process(ImageFixtures::pngHeaderOnly(100, 100));
    }

    public function testThumbnailRejectsAnImageThatDoesNotDecode(): void
    {
        $this->expectException(UndecodableImageException::class);

        (new ProgrammeImageProcessor())->thumbnail(ImageFixtures::pngHeaderOnly(100, 100));
    }

    /**
     * @return array<string, array{int, array{int, int}}>
     */
    public static function orientations(): array
    {
        return [
            'upright' => [1, [600, 300]],
            'mirrored' => [2, [600, 300]],
            'rotated 180' => [3, [600, 300]],
            'flipped' => [4, [600, 300]],
            'transposed' => [5, [300, 600]],
            'rotated 90 clockwise' => [6, [300, 600]],
            'transversed' => [7, [300, 600]],
            'rotated 90 counter-clockwise' => [8, [300, 600]],
        ];
    }

    /**
     * @param array{int, int} $displayedSize
     */
    #[DataProvider('orientations')]
    public function testExifOrientationTurnsTheFullAndTheThumbUpright(int $orientation, array $displayedSize): void
    {
        $source = ImageFixtures::quadrantJpegWithOrientation($orientation);
        $processor = new ProgrammeImageProcessor();

        $processed = $processor->process($source);

        self::assertSame([...$displayedSize, IMAGETYPE_WEBP], $this->sizeAndType($processed->full));
        self::assertUprightQuadrants($this->decode($processed->full));
        self::assertUprightQuadrants($this->decode($processed->thumb));
        self::assertUprightQuadrants($this->decode($processor->thumbnail($source)));
    }

    private function assertUprightQuadrants(GdImage $image): void
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $left = intdiv($width, 4);
        $right = $width - $left;
        $top = intdiv($height, 4);
        $bottom = $height - $top;

        self::assertSame(
            ['red', 'green', 'blue', 'white'],
            [
                $this->colourAt($image, $left, $top),
                $this->colourAt($image, $right, $top),
                $this->colourAt($image, $left, $bottom),
                $this->colourAt($image, $right, $bottom),
            ],
        );
    }

    /**
     * @return array{int, int, int}
     */
    private function sizeAndType(EncodedImage $image): array
    {
        $size = getimagesizefromstring($image->bytes);
        self::assertNotFalse($size);

        return [$size[0], $size[1], $size[2]];
    }

    private function decode(EncodedImage $image): GdImage
    {
        $decoded = imagecreatefromstring($image->bytes);
        self::assertNotFalse($decoded);

        return $decoded;
    }

    private function colourAt(GdImage $image, int $x, int $y): string
    {
        $rgb = imagecolorat($image, $x, $y);
        self::assertNotFalse($rgb);
        $red = ($rgb >> 16) & 0xFF;
        $green = ($rgb >> 8) & 0xFF;
        $blue = $rgb & 0xFF;

        return match (true) {
            $red > 180 && $green < 60 && $blue < 60 => 'red',
            $red < 60 && $green > 180 && $blue < 60 => 'green',
            $red < 60 && $green < 60 && $blue > 180 => 'blue',
            $red > 230 && $green > 230 && $blue > 230 => 'white',
            default => sprintf('rgb(%d, %d, %d)', $red, $green, $blue),
        };
    }
}
