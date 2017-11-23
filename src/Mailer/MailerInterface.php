<?php

namespace kissj\Mailer;


interface MailerInterface {
	public function sendMail($recipientEmail, $subject, $body);
}