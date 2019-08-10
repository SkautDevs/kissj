<?php

namespace kissj\Mailer;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class PhpMailerWrapper implements MailerInterface {

    private $smtp;
    private $smtp_server;
    private $smtp_port;
    private $smtp_auth;
    private $smtp_username;
    private $smtp_password;
    private $smtp_secure;
    private $from_mail;
    private $from_name;
    private $bcc_mail;
    private $bcc_name;
    private $disable_tls;
    private $debugOutputLevel;
    private $sendMailToMainRecipient;

    public function __construct(array $mailerSettings) {
        $this->smtp = $mailerSettings['smtp'];
        $this->smtp_server = $mailerSettings['smtp_server'];
        $this->smtp_auth = $mailerSettings['smtp_auth'];
        $this->smtp_port = $mailerSettings['smtp_port'];
        $this->smtp_username = $mailerSettings['from_mail'];
        $this->smtp_password = $mailerSettings['smtp_password'];
        $this->smtp_secure = $mailerSettings['smtp_secure'];
        $this->from_mail = $mailerSettings['from_mail'];
        $this->from_name = $mailerSettings['from_name'];
        $this->bcc_mail = $mailerSettings['bcc_mail'];
        $this->bcc_name = $mailerSettings['bcc_name'];
        $this->disable_tls = $mailerSettings['disable_tls'];
        $this->debugOutputLevel = $mailerSettings['debugOutoutLevel'];
        $this->sendMailToMainRecipient = $mailerSettings['sendMailToMainRecipient'];
    }

    public function sendMail($recipientEmail, $subject, $body): void {
        $mailer = new PHPMailer(true);

        try {
        $mailer->SMTPDebug = $this->debugOutputLevel; // Enable debug output
        if ($this->smtp) {
            $mailer->isSMTP();
        } else {
            $mailer->isMail();
        }
        if ($this->disable_tls) {
            $mailer->SMTPOptions = array (
                'ssl' => array (
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ),
            );
        }
        $mailer->Host = $this->smtp_server;    // Specify main and backup SMTP servers
        $mailer->Port = $this->smtp_port;    // TCP port to connect to
        $mailer->SMTPAuth = $this->smtp_auth;    // Enable SMTP authentication
        $mailer->Username = $this->smtp_username;    // SMTP username
        $mailer->Password = $this->smtp_password;    // SMTP password
            $mailer->SMTPSecure = $this->smtp_secure;    // Enable TLS encryption, `ssl` or null also accepted
        $mailer->CharSet = 'UTF-8';

        //Recipients
        $mailer->setFrom($this->from_mail, $this->from_name);
        if (!empty($this->bcc_mail)) {
            $mailer->addCC($this->bcc_mail, $this->bcc_name);
        }

        if ($this->sendMailToMainRecipient) {
            $mailer->addAddress($recipientEmail);
        }

        // Content
            $mailer->isHTML();
        $mailer->Subject = $subject;
        $mailer->Body = $body;
        $mailer->AltBody = strip_tags($body);

            $mailer->send();
        } catch (\Exception $e) {
            throw new Exception('Error sending email', $e->getCode(), $e);
        }
    }
}
