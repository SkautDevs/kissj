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

    public function info(string $message, array $params = []): void
    {
        $this->addMessage('info', $message, $params);
    }

    public function success(string $message, array $params = []): void
    {
        $this->addMessage('success', $message, $params);
    }

    public function warning(string $message, array $params = []): void
    {
        $this->addMessage('warning', $message, $params);
    }

    public function error(string $message, array $params = []): void
    {
        $this->addMessage('error', $message, $params);
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

    /**
     * @param array<string, string> $params
     */
    private function addMessage(string $type, string $message, array $params = []): void
    {
        if (!isset($_SESSION['flashMessages']) || !is_array($_SESSION['flashMessages'])) {
            $_SESSION['flashMessages'] = [];
        }

        $_SESSION['flashMessages'][] = [
            'type' => $type,
            'message' => $this->translator->trans($message, $params),
        ];
    }
}
