<?php

declare(strict_types=1);

namespace kissj\Programme\Image;

final readonly class EncodedImage
{
    /**
     * @param 'image/webp'|'image/jpeg' $contentType
     */
    public function __construct(
        public string $bytes,
        public string $contentType,
    ) {
    }
}
