<?php

namespace kissj\Mailer;

class MockMailer implements MailerInterface {

    public function sendMailFromTemplate($recipientEmail, $subject, $tempalteName, $parameters) {

    }
}
