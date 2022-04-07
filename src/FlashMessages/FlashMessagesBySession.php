<?php

declare(strict_types=1);

namespace kissj\FlashMessages;

class FlashMessagesBySession implements FlashMessagesInterface
{
    public function info(string $message): void
    {
        $_SESSION['flashMessages'][] = ['type' => 'info', 'message' => $message];
    }

    public function success(string $message): void
    {
        $_SESSION['flashMessages'][] = ['type' => 'success', 'message' => $message];
    }

    public function warning(string $message): void
    {
        $_SESSION['flashMessages'][] = ['type' => 'warning', 'message' => $message];
    }

    public function error(string $message): void
    {
        $_SESSION['flashMessages'][] = ['type' => 'error', 'message' => $message];
    }

    /**
     * @return string[]
     */
    public function dumpMessagesIntoArray(): array
    {
        $messages = $_SESSION['flashMessages'] ?? [];
        $_SESSION['flashMessages'] = [];

        return $messages;
    }
}
