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
        $_SESSION['flashMessages'][] = [
            'type' => 'info',
            'message' => $this->translator->trans($message),
        ];
    }

    public function success(string $message): void
    {
        $_SESSION['flashMessages'][] = [
            'type' => 'success',
            'message' => $this->translator->trans($message),
        ];
    }

    public function warning(string $message): void
    {
        $_SESSION['flashMessages'][] = [
            'type' => 'warning',
            'message' => $this->translator->trans($message),
        ];
    }

    public function error(string $message): void
    {
        $_SESSION['flashMessages'][] = [
            'type' => 'error',
            'message' => $this->translator->trans($message),
        ];
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
