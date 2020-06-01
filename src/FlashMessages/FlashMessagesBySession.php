<?php

namespace kissj\FlashMessages;


class FlashMessagesBySession implements FlashMessagesInterface {
    public function info($message): void {
        $_SESSION['flashMessages'][] = ['type' => 'info', 'message' => $message];
    }

    public function success($message): void {
        $_SESSION['flashMessages'][] = ['type' => 'success', 'message' => $message];
    }

    public function warning($message): void {
        $_SESSION['flashMessages'][] = ['type' => 'warning', 'message' => $message];
    }

    public function error($message): void {
        $_SESSION['flashMessages'][] = ['type' => 'error', 'message' => $message];
    }

    public function dumpMessagesIntoArray(): array {
        $messages = $_SESSION['flashMessages'] ?? [];
        $_SESSION['flashMessages'] = [];

        return $messages;
    }
}
