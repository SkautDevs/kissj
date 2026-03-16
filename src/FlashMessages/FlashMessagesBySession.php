<?php

declare(strict_types=1);

namespace kissj\FlashMessages;

use Symfony\Contracts\Translation\TranslatorInterface;

class FlashMessagesBySession implements FlashMessagesInterface
{
    public function __construct(
        protected TranslatorInterface $translator,
    ) {
    }

    public function info(string $message): void
    {
        $this->addMessage('info', $message);
    }

    public function success(string $message): void
    {
        $this->addMessage('success', $message);
    }

    public function warning(string $message): void
    {
        $this->addMessage('warning', $message);
    }

    public function error(string $message): void
    {
        $this->addMessage('error', $message);
    }

    /**
     * @return array<array{type: string, message: string}>
     */
    public function dumpMessagesIntoArray(): array
    {
        /** @var array<array{type: string, message: string}> $messages */
        $messages = $_SESSION['flashMessages'] ?? [];
        $_SESSION['flashMessages'] = [];

        return $messages;
    }

    private function addMessage(string $type, string $message): void
    {
        if (!isset($_SESSION['flashMessages']) || !is_array($_SESSION['flashMessages'])) {
            $_SESSION['flashMessages'] = [];
        }

        $_SESSION['flashMessages'][] = [
            'type' => $type,
            'message' => $this->translator->trans($message),
        ];
    }
}
