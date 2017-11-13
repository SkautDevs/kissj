<?php

namespace kissj\Mailer;


interface MailerInterface {
	public function sendMail($recipient, $subject, $body);
}