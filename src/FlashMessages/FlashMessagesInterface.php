<?php

declare(strict_types=1);

namespace kissj\FlashMessages;

interface FlashMessagesInterface
{
    public function info(string $message): void;

    public function success(string $message): void;

    public function warning(string $message): void;

    public function error(string $message): void;

    /**
     * @return string[]
     */
    public function dumpMessagesIntoArray(): array;
}
