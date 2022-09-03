<?php

namespace kissj\Mailer;

use kissj\Event\Event;

class MailerSettings
{
    private Event $event;
    private string $fullUrlLink;

    public function __construct(
        public string $mailDsn,
        public string $smtp,
        public string $smtpServer,
        public string $smtpAuth,
        public string $smtpPort,
        public string $smtpUsername,
        public string $smtpPassword,
        public string $smtpSecure,
        public string $disableTls,
        public string $debugOutputLevel,
        public string $sendMailToMainRecipient,
    ) {
    }

    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function setFullUrlLink(string $urlLink): void
    {
        $this->fullUrlLink = $urlLink;
    }

    public function getFullUrlLink(): string
    {
        return $this->fullUrlLink;
    }
}
