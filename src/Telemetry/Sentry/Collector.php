<?php

declare(strict_types=1);

namespace kissj\Telemetry\Sentry;

use Throwable;
use Sentry\State\Hub;
use Sentry\State\Scope;

readonly class Collector
{
    public function __construct(
        private Hub $sentryHub,
    ) {
    }

    public function collect(Throwable $t): void
    {
        // Sentry client is wrapped in Sentry\State\Hub::withScope(...) to pass HTTP, Event and User context
        $this->sentryHub->withScope(function (Scope $scope) use ($t): void {
            $scope->setFingerprint([$t::class, $t->getFile(), (string)$t->getLine()]);

            $this->sentryHub->captureException($t);
        });
    }
}
