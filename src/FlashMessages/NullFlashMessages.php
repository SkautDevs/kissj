<?php

declare(strict_types=1);

namespace kissj\FlashMessages;

/**
 * Discards every message. Used when an eligibility check needs to run for its boolean result only,
 * without surfacing its warnings to the user (e.g. a POST that will re-run the check on the GET redirect).
 */
class NullFlashMessages implements FlashMessagesInterface
{
    public function info(string $message, array $params = []): void
    {
    }

    public function success(string $message, array $params = []): void
    {
    }

    public function warning(string $message, array $params = []): void
    {
    }

    public function error(string $message, array $params = []): void
    {
    }

    public function dumpMessagesIntoArray(): array
    {
        return [];
    }
}
