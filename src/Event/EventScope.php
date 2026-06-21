<?php

declare(strict_types=1);

namespace kissj\Event;

use kissj\Mailer\MailerSettings;
use kissj\Translation\CurrentTranslator;
use kissj\Translation\TranslatorFactory;
use Slim\Views\Twig;

readonly class EventScope
{
    public function __construct(
        private Twig $view,
        private MailerSettings $mailerSettings,
        private CurrentTranslator $translator,
        private TranslatorFactory $translatorFactory,
    ) {
    }

    public function apply(Event $event, string $fullUrlLink): void
    {
        $this->view->getEnvironment()->addGlobal('event', $event);
        $this->mailerSettings->setEvent($event);
        $this->mailerSettings->setFullUrlLink($fullUrlLink);
        $this->translator->setDelegate(
            $this->translatorFactory->createForEventType($event->getEventType()),
        );
    }

    public function resetToBase(): void
    {
        $this->view->getEnvironment()->addGlobal('event', null);
        $this->translator->setDelegate($this->translatorFactory->createBase());
    }
}
