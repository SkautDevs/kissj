<?php

declare(strict_types=1);

namespace Tests\Functional;

use Tests\AppTestCase;

class ApplicationTimezoneTest extends AppTestCase
{
    public function testAppRunsInBerlinTimezone(): void
    {
        date_default_timezone_set('UTC');

        $this->getTestApp();

        self::assertSame('Europe/Berlin', date_default_timezone_get());
    }
}
