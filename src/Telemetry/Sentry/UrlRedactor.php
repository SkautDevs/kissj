<?php

declare(strict_types=1);

namespace kissj\Telemetry\Sentry;

use Sentry\Event;

readonly class UrlRedactor
{
    public const string REDACTED = '<url-redacted>';

    public static function redact(string $value): string
    {
        $value = preg_replace('~/tryLogin/[^/?#]+~', '/tryLogin/' . self::REDACTED, $value);
        if ($value === null) {
            return self::REDACTED;
        }

        $value = preg_replace('~(^|[?&])ot_token=[^&#]*~', '${1}ot_token=' . self::REDACTED, $value);
        if ($value === null) {
            return self::REDACTED;
        }

        return $value;
    }

    public static function scrub(Event $event): Event
    {
        $transaction = $event->getTransaction();
        if ($transaction !== null) {
            $event->setTransaction(self::redact($transaction));
        }

        $request = $event->getRequest();
        foreach (['url', 'query_string'] as $key) {
            $value = $request[$key] ?? null;
            if (is_string($value)) {
                $request[$key] = self::redact($value);
            }
        }

        $data = $request['data'] ?? null;
        if (is_array($data) && array_key_exists('ot_token', $data)) {
            $data['ot_token'] = self::REDACTED;
            $request['data'] = $data;
        }

        $headers = $request['headers'] ?? null;
        if (is_array($headers)) {
            $request['headers'] = self::redactHeaders($headers);
        }

        $event->setRequest($request);

        $httpContext = $event->getContexts()['http'] ?? null;
        if (is_array($httpContext)) {
            $url = $httpContext['url'] ?? null;
            if (is_string($url)) {
                $httpContext['url'] = self::redact($url);
                $event->setContext('http', $httpContext);
            }
        }

        $message = $event->getMessage();
        if ($message !== null) {
            $formatted = $event->getMessageFormatted();
            $event->setMessage(
                self::redact($message),
                array_map(self::redact(...), $event->getMessageParams()),
                $formatted === null ? null : self::redact($formatted),
            );
        }

        return $event;
    }

    /**
     * @param array<mixed> $headers
     * @return array<mixed>
     */
    private static function redactHeaders(array $headers): array
    {
        foreach ($headers as $name => $value) {
            if (is_string($value)) {
                $headers[$name] = self::redact($value);
                continue;
            }

            if (is_array($value)) {
                $headers[$name] = array_map(
                    static fn (mixed $item): mixed => is_string($item) ? self::redact($item) : $item,
                    $value,
                );
            }
        }

        return $headers;
    }
}
