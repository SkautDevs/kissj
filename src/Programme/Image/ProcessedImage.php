<?php

declare(strict_types=1);

namespace kissj\Programme\Image;

final readonly class ProcessedImage
{
    public function __construct(
        public EncodedImage $full,
        public EncodedImage $thumb,
    ) {
    }
}
