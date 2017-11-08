<?php

namespace kissj\Mailer;

class PhpMailerWrapper implements MailerInterface {
	
	private $smtp;
	private $smtp_server;
	private $smtp_port;
	private $smtp_username;
	private $smtp_password;
	private $from_mail;
	private $from_name;
	private $bcc_mail;
	private $bcc_name;
	
	public function __construct($mailerSettings) {
		$this->smtp = $mailerSettings['smtp'];
		$this->smtp_server = $mailerSettings['smtp_server'];
		$this->smtp_port = $mailerSettings['smtp_port'];
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
		if ($this->smtp) {
			$mailer->isSMTP();    // Set mailer to use SMTP
		} else {
			$mailer->isMail();
		}
		$mailer->Host = $this->smtp_server;    // Specify main and backup SMTP servers
		$mailer->SMTPAuth = true;    // Enable SMTP authentication
		$mailer->Username = $this->smtp_username;    // SMTP username
		$mailer->Password = $this->smtp_password;    // SMTP password
		$mailer->SMTPSecure = 'tls';    // Enable TLS encryption, `ssl` also accepted
		$mailer->Port = $this->smtp_port;    // TCP port to connect to
		
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