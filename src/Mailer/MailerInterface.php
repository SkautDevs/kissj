<?php

namespace kissj\Mailer;

interface MailerInterface {
    public function sendMailFromTemplate(
        string $recipientEmail,
        string $subject,
        string $templateName,
        array $parameters
    );
}
