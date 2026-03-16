<?php

declare(strict_types=1);

namespace kissj\FlashMessages;

interface FlashMessagesInterface
{
    /**
     * @param array<string, string> $params
     */
    public function info(string $message, array $params = []): void;

    /**
     * @param array<string, string> $params
     */
    public function success(string $message, array $params = []): void;

    /**
     * @param array<string, string> $params
     */
    public function warning(string $message, array $params = []): void;

    /**
     * @param array<string, string> $params
     */
    public function error(string $message, array $params = []): void;

    /**
     * @return array<array{type: string, message: string}>
     */
    public function dumpMessagesIntoArray(): array;
}
