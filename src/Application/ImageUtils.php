<?php

declare(strict_types=1);

namespace kissj\Application;

use Throwable;

class ImageUtils
{
    public static function getLocalImageInBase64(string $pathWithLeadingSlash): string
    {
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
}
