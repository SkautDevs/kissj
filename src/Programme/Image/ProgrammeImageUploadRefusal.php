<?php

declare(strict_types=1);

namespace kissj\Programme\Image;

enum ProgrammeImageUploadRefusal
{
    case TooManyPixels;
    case Undecodable;
}
