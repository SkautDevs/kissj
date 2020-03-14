<?php

namespace kissj\Mailer;

use kissj\Participant\Participant;
use kissj\Payment\Payment;
use kissj\User\User;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Slim\Views\Twig;

class PhpMailerWrapper {
    private $renderer;
    private $eventName;

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

    public function __construct(Twig $renderer, array $mailerSettings) {
        $this->renderer = $renderer;
        $this->eventName = 'AQUA 2020'; // TODO make dynamic

        // TODO refactor
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

    public function sendLoginToken(User $user, string $link) {
        $this->sendMailFromTemplate(
            $user->email,
            'link with login',
            'login-token',
            ['link' => $link, 'event' => $user->event]
        );
    }

    public function sendRegistrationClosed(User $user): void {
        $this->sendMailFromTemplate($user->email, 'registration sent', 'closed', []);
    }

    public function sendDeniedRegistration(Participant $participant, string $reason) {
        $this->sendMailFromTemplate(
            $participant->user->email,
            'registration returned',
            'denial',
            ['reason' => $reason, 'event' => $participant->user->event]
        );
    }

    public function sendRegistrationApprovedWithPayment(Participant $participant, Payment $payment) {
        $this->sendMailFromTemplate(
            $participant->user->email,
            'payment informations',
            'payment-info',
            [
                'event' => $participant->user->event,
                'participant' => $participant,
                'payment' => $payment,
            ]
        );
    }

    public function sendGuestRegistrationFinished(Participant $participant) {
        $this->sendMailFromTemplate(
            $participant->user->email,
            'registration finished',
            'finished',
            [
                'event' => $participant->user->event,
                'participant' => $participant,
            ]
        );
    }

    public function sendCancelledPayment(Participant $participant, string $reason) {
        $this->sendMailFromTemplate(
            $participant->user->email,
            'payment cancelled',
            'cancel-payment',
            ['reason' => $reason, 'event' => $participant->user->event]
        );
    }

    public function sendRegistrationPaid(Participant $participant) {
        $this->sendMailFromTemplate(
            $participant->user->email,
            'payment successfully paid',
            'payment-successful',
            ['event' => $participant->user->event]
        );
    }

    public function sendWelcomeFreeParticipantMessage(Participant $participant) {
        $this->sendMailFromTemplate(
            $participant->user->email,
            'registration confirmed',
            'welcome-message-free-participant',
            ['event' => $participant->user->event]
        );
    }

    private function sendMailFromTemplate(
        string $recipientEmail,
        string $subject,
        string $templateName,
        array $parameters
    ): void {
        $messageBody = $this->renderer->fetch('emails/'.$templateName.'.twig', $parameters);
        $mailer = new PHPMailer(true);

        try {
            $mailer->SMTPDebug = $this->debugOutputLevel; // Enable debug output
            if ($this->smtp) {
                $mailer->isSMTP();
            } else {
                $mailer->isMail();
            }
            if ($this->disable_tls) {
                $mailer->SMTPOptions = array(
                    'ssl' => array(
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
            $mailer->Subject = $this->eventName.' - '.$subject;
            $mailer->Body = $messageBody;
            $mailer->AltBody = strip_tags($messageBody);

            $mailer->send();
        } catch (\Exception $e) {
            throw new Exception('Error sending email', $e->getCode(), $e);
        }
    }
}
