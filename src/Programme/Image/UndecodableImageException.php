<?php

declare(strict_types=1);

namespace kissj\Programme\Image;

use RuntimeException;

/**
 * The bytes look like an image (the header sniff passed) but GD cannot decode them.
 */
class UndecodableImageException extends RuntimeException
{
}
