<?php

declare(strict_types=1);

namespace kissj\Participant\Admin;

enum AdminValuesSaveResult
{
    case Saved;
    case UnknownSubcamp;
    case UniqueIdTaken;
}
