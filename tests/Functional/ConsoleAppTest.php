<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\ApplicationGetter;
use Tests\AppTestCase;

class ConsoleAppTest extends AppTestCase
{
    public function testConsoleAppRegistersUpdatePaymentsCommand(): void
    {
        $consoleApp = (new ApplicationGetter())->getConsoleApp(
            __DIR__ . '/../',
            'env.testing',
            $this->getRunTempPath(),
        );

        self::assertTrue($consoleApp->has('app:update-payments'));
    }
}
