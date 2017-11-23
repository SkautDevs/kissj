<?php

namespace kissj\Mailer;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class MockMailer implements \kissj\Mailer\MailerInterface {


	public function sendMail($recipient, $subject, $body) {

	}
}