<?php

declare(strict_types=1);

namespace kissj\Deal;

class EventDeal
{
    public function __construct(
        public readonly string $slug,
        public readonly string $address,
    ) {
    }
}
