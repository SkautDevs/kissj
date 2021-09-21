<?php

namespace kissj\Mailer;

class MailerSettings {
    public function __construct(
        public string $smtp,
        public string $smtpServer,
        public string $smtpAuth,
        public string $smtpPort,
        public string $smtpUsername,
        public string $smtpPassword,
        public string $smtpSecure,
        public string $fromMail,
        public string $fromName,
        public string $bccMail,
        public string $bccName,
        public string $disableTls,
        public string $debugOutputLevel,
        public string $sendMailToMainRecipient,
    ) {
    }
}
