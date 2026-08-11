<?php

declare(strict_types=1);

namespace kissj\FlashMessages;

/**
 * Discards every message. Used when an eligibility check runs for its boolean result only.
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
