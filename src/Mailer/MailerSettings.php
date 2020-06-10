<?php

namespace kissj\Mailer;

class MailerSettings {
    public $smtp;
    public $smtpServer;
    public $smtpAuth;
    public $smtpPort;
    public $smtpUsername;
    public $smtpPassword;
    public $smtpSecure;
    public $fromMail;
    public $fromName;
    public $bccMail;
    public $bccName;
    public $disableTls;
    public $debugOutputLevel;
    public $sendMailToMainRecipient;

    public function __construct(
        string $smtp,
        string $smtpServer,
        string $smtpAuth,
        string $smtpPort,
        string $smtpUsername,
        string $smtpPassword,
        string $smtpSecure,
        string $fromMail,
        string $fromName,
        string $bccMail,
        string $bccName,
        string $disableTls,
        string $debugOutputLevel,
        string $sendMailToMainRecipient
    ) {
        $this->smtp = $smtp;
        $this->smtpServer = $smtpServer;
        $this->smtpAuth = $smtpAuth;
        $this->smtpPort = $smtpPort;
        $this->smtpUsername = $smtpUsername;
        $this->smtpPassword = $smtpPassword;
        $this->smtpSecure = $smtpSecure;
        $this->fromMail = $fromMail;
        $this->fromName = $fromName;
        $this->bccMail = $bccMail;
        $this->bccName = $bccName;
        $this->disableTls = $disableTls;
        $this->debugOutputLevel = $debugOutputLevel;
        $this->sendMailToMainRecipient = $sendMailToMainRecipient;
    }
}
