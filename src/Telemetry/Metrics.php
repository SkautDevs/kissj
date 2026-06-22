<?php

declare(strict_types=1);

namespace kissj\Telemetry;

use Sentry\Unit;

use function Sentry\traceMetrics;

readonly class Metrics
{
    /**
     * @param array<string, scalar> $attributes
     */
    public function count(
        MetricName $name,
        int $value = 1,
        array $attributes = [],
    ): void {
        traceMetrics()->count($name->value, $value, $attributes);
    }

    /**
     * @param array<string, scalar> $attributes
     */
    public function distribution(
        MetricName $name,
        float|int $value,
        array $attributes = [],
        ?Unit $unit = null
    ): void {
        traceMetrics()->distribution($name->value, $value, $attributes, $unit);
    }

    /**
     * @param array<string, scalar> $attributes
     */
    public function distributionMs(
        MetricName $name,
        float|int $value,
        array $attributes = [],
    ): void {
        traceMetrics()->distribution($name->value, $value, $attributes, Unit::millisecond());
    }

    public function flush(): void
    {
        traceMetrics()->flush();
    }
}
