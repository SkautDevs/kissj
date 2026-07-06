<?php

declare(strict_types=1);

namespace kissj\Application;

use Throwable;

class ImageUtils
{
    public static function getLocalImageInBase64(string $pathWithLeadingSlash): string
    {
        if ($pathWithLeadingSlash === '') {
            return '';
        }

        try {
            $logo = file_get_contents(__DIR__ . '/../../public' . $pathWithLeadingSlash);
        } catch (Throwable) {
            $logo = false;
        }

        if ($logo === false) {
            return '';
        }

        return base64_encode($logo);
    }

    /**
     * Whether the given raw bytes are a raster image we accept for upload. Sniffs the actual content
     * (getimagesizefromstring), never a client-supplied media type, so a scripted SVG or HTML payload
     * disguised with an image Content-Type is rejected.
     */
    public static function isSupportedRasterImage(string $binaryContents): bool
    {
        if ($binaryContents === '') {
            return false;
        }

        $imageInfo = @getimagesizefromstring($binaryContents);
        if ($imageInfo === false) {
            return false;
        }

        return in_array($imageInfo[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF], true);
    }
}
