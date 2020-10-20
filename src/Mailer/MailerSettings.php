<?php

namespace kissj\Mailer;

class MailerSettings {
    public string $smtp;
    public string $smtpServer;
    public string $smtpAuth;
    public string $smtpPort;
    public string $smtpUsername;
    public string $smtpPassword;
    public string $smtpSecure;
    public string $fromMail;
    public string $fromName;
    public string $bccMail;
    public string $bccName;
    public string $disableTls;
    public string $debugOutputLevel;
    public string $sendMailToMainRecipient;

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
