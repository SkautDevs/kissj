<?php

declare(strict_types=1);

namespace kissj\Telemetry\Sentry;

use Sentry\SentrySdk;
use Sentry\Tracing\Span;
use Sentry\Tracing\SpanStatus;
use Sentry\Tracing\Transaction;
use Sentry\Tracing\TransactionContext;
use Sentry\Tracing\TransactionSource;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function Sentry\captureException;

class ConsoleSubscriber implements EventSubscriberInterface
{
    /** @var list<array{transaction: Transaction, previousSpan: ?Span}> */
    private array $stack = [];

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => 'onCommand',
            ConsoleEvents::ERROR => 'onError',
            ConsoleEvents::TERMINATE => 'onTerminate',
        ];
    }

    public function onCommand(ConsoleCommandEvent $event): void
    {
        $name = $event->getCommand()?->getName() ?? 'unknown';

        $context = TransactionContext::make()
            ->setName($name)
            ->setSource(TransactionSource::task())
            ->setOp('console.command');

        $hub = SentrySdk::getCurrentHub();
        $previousSpan = $hub->getSpan();
        $transaction = $hub->startTransaction($context);
        $hub->setSpan($transaction);
        $this->stack[] = ['transaction' => $transaction, 'previousSpan' => $previousSpan];
    }

    public function onError(ConsoleErrorEvent $event): void
    {
        captureException($event->getError());

        $lastKey = array_key_last($this->stack);
        if ($lastKey === null) {
            return;
        }

        $this->stack[$lastKey]['transaction']->setStatus(SpanStatus::internalError());
    }

    public function onTerminate(ConsoleTerminateEvent $event): void
    {
        $frame = array_pop($this->stack);
        if ($frame === null) {
            return;
        }

        $frame['transaction']->setData(['exit_code' => $event->getExitCode()]);
        $frame['transaction']->finish();
        SentrySdk::getCurrentHub()->setSpan($frame['previousSpan']);
    }
}
