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

    public function collect(Throwable $e): Throwable
    {
        // Sentry client is wrapped in Sentry\State\Hub::withScope(...) to pass HTTP, Event and User context
        $this->sentryHub->withScope(function (Scope $scope) use ($e): void {
            $scope->setFingerprint([md5($e::class)]);

            $this->sentryHub->captureException($e);
        });

        return $e;
    }
}
