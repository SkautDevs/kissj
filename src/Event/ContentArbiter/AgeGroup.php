<?php

declare(strict_types=1);

namespace kissj\Event\ContentArbiter;

enum AgeGroup: string
{
    case Under18 = 'under18';
    case Over18 = 'over18';
}
