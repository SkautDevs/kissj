<?php

declare(strict_types=1);

namespace kissj\Logging\Sentry;

use Throwable;
use Sentry\State\Hub;
use Sentry\State\Scope;

readonly class SentryCollector
{
    public function __construct(
        private Hub $sentryHub,
    ) {
    }

    public function collect(Throwable $t): void
    {
        // Sentry client is wrapped in Sentry\State\Hub::withScope(...) to pass HTTP, Event and User context
        $this->sentryHub->withScope(function (Scope $scope) use ($t): void {
            $scope->setFingerprint([md5($t::class)]);

            $this->sentryHub->captureException($t);
        });
    }
}
