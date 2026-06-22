<?php

declare(strict_types=1);

namespace kissj\Telemetry\Sentry;

use Dibi\DriverException;
use Dibi\Event;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanContext;
use Sentry\Tracing\SpanStatus;

class DibiSpanListener
{
    public function __invoke(Event $event): void
    {
        $parent = SentrySdk::getCurrentHub()->getSpan();
        // Unsampled transactions still have a Span object but a null SpanRecorder,
        // so child spans get discarded silently. Bail on requests that produce no observable output.
        if ($parent === null || $parent->getSampled() !== true) {
            return;
        }

        $operation = self::operation($event);
        $isQuery = match ($event->type) {
            Event::CONNECT, Event::BEGIN, Event::COMMIT, Event::ROLLBACK => false,
            default => true,
        };
        $description = ($isQuery && $event->sql !== '') ? SqlParameterizer::parameterize($event->sql) : null;

        $context = (new SpanContext())
            ->setOp(match ($event->type) {
                Event::CONNECT => 'db.connect',
                Event::BEGIN => 'db.tx.begin',
                Event::COMMIT => 'db.tx.commit',
                Event::ROLLBACK => 'db.tx.rollback',
                default => 'db.query',
            })
            ->setDescription($description ?? ($operation ?? 'db'))
            ->setData(self::data($event, $operation, $description));

        $endTimestamp = microtime(true);
        $startTimestamp = $endTimestamp - $event->time;
        $context->setStartTimestamp($startTimestamp);

        $span = $parent->startChild($context);
        if ($event->result instanceof DriverException) {
            $span->setStatus(SpanStatus::internalError());
        }
        $span->finish($endTimestamp);
    }

    /** @return array<string, scalar|null> */
    private static function data(Event $event, ?string $operation, ?string $description): array
    {
        $data = ['db.system' => 'postgresql'];
        if ($operation !== null) {
            $data['db.operation'] = $operation;
        }
        if ($description !== null) {
            $data['db.statement'] = $description;
        }
        if ($event->count !== null) {
            $data['db.rows_affected'] = $event->count;
        }

        return $data;
    }

    private static function operation(Event $event): ?string
    {
        $byType = match ($event->type) {
            Event::SELECT => 'SELECT',
            Event::INSERT => 'INSERT',
            Event::UPDATE => 'UPDATE',
            Event::DELETE => 'DELETE',
            default => null,
        };
        if ($byType !== null) {
            return $byType;
        }
        if ($event->sql === '') {
            return null;
        }
        if (preg_match('/^\s*([A-Za-z]+)/', $event->sql, $m) !== 1) {
            return null;
        }
        $keyword = strtoupper($m[1]);

        return $keyword === 'WITH' ? 'SELECT' : $keyword;
    }
}
