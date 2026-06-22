<?php

declare(strict_types=1);

namespace kissj\Skautis;

use kissj\Participant\Country;
use kissj\Participant\Gender;

readonly class SkautisPersonAdditionalDetails
{
    public function __construct(
        public Gender $gender,
        public Country $country,
    ) {
    }
}
