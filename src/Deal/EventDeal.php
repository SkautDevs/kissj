<?php

declare(strict_types=1);

namespace kissj\Deal;

readonly class EventDeal
{
    public function __construct(
        public string $slug,
        public string $address,
    ) {
    }
}
