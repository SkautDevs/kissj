<?php

namespace kissj\Mailer;

class PhpMailerWrapper implements MailerInterface {
	
	public $smtp_server;
	public $smtp_username;
	public $smtp_password;
	public $from_mail;
	public $from_name;
	public $bcc_mail;
	public $bcc_name;
	
	public function __construct($mailerSettings) {
		$this->smtp_server = $mailerSettings['smtp_server'];
		$this->smtp_username = $mailerSettings['smtp_password'];
		$this->smtp_password = $mailerSettings['from_mail'];
		$this->from_mail = $mailerSettings['from_mail'];
		$this->from_name = $mailerSettings['from_name'];
		$this->bcc_mail = $mailerSettings['from_mail'];
		$this->bcc_name = $mailerSettings['bcc_name'];
	}
	
	public function sendMail($recipient, $subject, $body) {
		$mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
		
		//Server settings
		$mailer->SMTPDebug = 2;    // Enable verbose debug output
		$mailer->isSMTP();    // Set mailer to use SMTP
		$mailer->Host = $this->smtp_server;    // Specify main and backup SMTP servers
		$mailer->SMTPAuth = true;    // Enable SMTP authentication
		$mailer->Username = $this->smtp_username;    // SMTP username
		$mailer->Password = $this->smtp_password;    // SMTP password
		$mailer->SMTPSecure = 'tls';    // Enable TLS encryption, `ssl` also accepted
		$mailer->Port = 587;    // TCP port to connect to
		
		//Recipients
		$mailer->setFrom($this->from_mail, $this->from_name);
		$mailer->addCC($this->bcc_mail, $this->bcc_name);
		
		// Content
		$mailer->isHTML(true);
		
		$mailer->Subject = $subject;
		$mailer->Body = $body;
		$mailer->AltBody = strip_tags($body);
		$mailer->send();
		
	}
}