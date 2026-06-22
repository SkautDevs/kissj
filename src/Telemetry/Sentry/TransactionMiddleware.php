<?php

declare(strict_types=1);

namespace kissj\Telemetry\Sentry;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sentry\SentrySdk;
use Sentry\Tracing\TransactionSource;
use Throwable;

use function Sentry\continueTrace;

readonly class TransactionMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        $normalized = preg_replace('@(?<=/)\d+(?=/|$)@', '{id}', $path) ?? $path;

        $context = continueTrace(
            $request->getHeaderLine('sentry-trace'),
            $request->getHeaderLine('baggage'),
        )
            ->setName($request->getMethod() . ' ' . $normalized)
            ->setSource(TransactionSource::route())
            ->setOp('http.server');

        $hub = SentrySdk::getCurrentHub();
        $previousSpan = $hub->getSpan();
        $transaction = $hub->startTransaction($context);
        $hub->setSpan($transaction);

        try {
            $response = $handler->handle($request);
            $transaction->setHttpStatus($response->getStatusCode());

            return $response;
        } catch (Throwable $e) {
            $transaction->setHttpStatus(500);

            throw $e;
        } finally {
            $transaction->finish();
            $hub->setSpan($previousSpan);
        }
    }
}
