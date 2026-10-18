<?php

declare(strict_types=1);

namespace Fixture\Phpstan;

use DateTimeImmutable;

function datetimeConstructor(): DateTimeImmutable
{
    return new DateTimeImmutable();
}
