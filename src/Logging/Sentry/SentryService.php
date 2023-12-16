<?php

declare(strict_types=1);

namespace kissj\Logging\Sentry;

use Sentry\Tracing\Span;
use Sentry\Tracing\Transaction;
use Sentry\Tracing\TransactionContext;
use Throwable;
use Sentry\State\Hub;
use Sentry\State\Scope;

class SentryService
{
    public function __construct(
        private readonly Hub $sentryHub,
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

    public function startTransaction(string $transactionName): Transaction
    {
        $transaction = $this->sentryHub->startTransaction(new TransactionContext($transactionName));
        $this->sentryHub->setSpan($transaction);

        return $transaction;
    }

    public function startSpan(string $operationName, Transaction $parent): Span
    {
        $child = $parent->startChild((new TransactionContext($operationName))->setOp($operationName));
        $this->sentryHub->setSpan($child);

        return $child;
    }

    public function endSpan(Transaction $transaction, Transaction $parent): Transaction
    {
        $transaction->finish();
        $this->sentryHub->setSpan($parent);

        return $parent;
    }
    
    public function endTransaction(Transaction $transaction): void
    {
        $transaction->finish();
    }
}
