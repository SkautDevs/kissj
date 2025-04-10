<?php

namespace kissj\Mailer;

use kissj\Event\Event;

class MailerSettings
{
    private Event $event;
    private string $fullUrlLink;

    public function __construct(
        readonly public string $mailDsn,
        readonly public string $devMail = 'devs@skaut.cz',
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
