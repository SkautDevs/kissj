<?php

namespace kissj\Mailer;

use kissj\Event\Event;

class MailerSettings
{
    private Event $event;

    public function __construct(
        public string $smtp,
        public string $smtpServer,
        public string $smtpAuth,
        public string $smtpPort,
        public string $smtpUsername,
        public string $smtpPassword,
        public string $smtpSecure,
        public string $bccMail,
        public string $bccName,
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
}
