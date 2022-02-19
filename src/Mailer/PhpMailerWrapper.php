<?php declare(strict_types=1);

namespace kissj\Mailer;

use kissj\Participant\Participant;
use kissj\Payment\Payment;
use kissj\User\User;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Slim\Views\Twig;
use Symfony\Contracts\Translation\TranslatorInterface;

class PhpMailerWrapper
{
    public function __construct(
        private Twig $renderer,
        private MailerSettings $settings,
        private TranslatorInterface $translator,
    ) {
    }

    public function sendLoginToken(User $user, string $link): void
    {
        $this->sendMailFromTemplate(
            $user->email,
            $this->translator->trans('email.login-token.subject'),
            'login-token',
            ['link' => $link, 'event' => $user->event],
        );
    }

    public function sendRegistrationClosed(User $user): void
    {
        $this->sendMailFromTemplate(
            $user->email,
            $this->translator->trans('email.closed.subject'),
            'closed',
            [],
        );
    }

    public function sendDeniedRegistration(Participant $participant, string $reason): void
    {
        $user = $participant->getUserButNotNull();
        $this->sendMailFromTemplate(
            $user->email,
            $this->translator->trans('email.denial.subject'),
            'denial',
            ['reason' => $reason, 'event' => $user->event],
        );
    }

    public function sendRegistrationApprovedWithPayment(Participant $participant, Payment $payment): void
    {
        $user = $participant->getUserButNotNull();
        $this->sendMailFromTemplate(
            $user->email,
            $this->translator->trans('email.payment-info.subject'),
            'payment-info',
            [
                'event' => $user->event,
                'participant' => $participant,
                'payment' => $payment,
            ]
        );
    }

    public function sendRegistrationApprovedForForeignContingents(Participant $participant): void
    {
        $user = $participant->getUserButNotNull();
        $this->sendMailFromTemplate(
            $user->email,
            $this->translator->trans('email.payment-info.subject'),
            'payment-info-contingents',
            ['event' => $user->event],
        );
    }

    public function sendCancelledPayment(Participant $participant, string $reason): void
    {
        $user = $participant->getUserButNotNull();
        $this->sendMailFromTemplate(
            $user->email,
            $this->translator->trans('email.cancel-payment.subject'),
            'cancel-payment',
            ['reason' => $reason, 'event' => $user->event],
        );
    }

    public function sendRegistrationPaid(Participant $participant): void
    {
        $user = $participant->getUserButNotNull();
        $this->sendMailFromTemplate(
            $user->email,
            $this->translator->trans('email.payment-successful.subject'),
            'payment-successful',
            ['event' => $user->event],
        );
    }

    public function sendGuestRegistrationFinished(Participant $participant): void
    {
        $user = $participant->getUserButNotNull();
        $this->sendMailFromTemplate(
            $user->email,
            $this->translator->trans('email.finished.subject'),
            'finished',
            ['event' => $user->event, 'participant' => $participant],
        );
    }

    public function sendPaymentTransferedFromYou(Participant $participant): void
    {
        $this->sendMailFromTemplate(
            $participant->getUserButNotNull()->email,
            $this->translator->trans('email.payment-transfered-from-you.subject'),
            'payment-transfered-from-you',
            [],
        );
    }

    public function sendDuePaymentDenied(Participant $participant): void
    {// TODO improve
        $user = $participant->getUserButNotNull();
        $this->sendMailFromTemplate(
            $user->email,
            'platba neobdržena -> registrace zrušena', // TODO make translatable
            'cancel-payment',
            ['reason' => 'neobdrželi jsme tvou platbu v termínu pro zaplacení', 'event' => $user->event],
        );
    }

    /**
     * @param string               $recipientEmail
     * @param string               $subject
     * @param string               $templateName
     * @param array<string, mixed> $parameters
     * @return void
     */
    private function sendMailFromTemplate(
        string $recipientEmail,
        string $subject,
        string $templateName,
        array $parameters
    ): void {
        $messageBody = $this->renderer->fetch(
            'emails/' . $templateName . '.twig',
            array_merge($parameters, ['fullRegistrationLink' => $this->settings->getFullUrlLink()]),
        );
        $mailer = new PHPMailer(true);
        $event = $this->settings->getEvent();

        try {
            // phpamiler echoing debug, content-length middleware addds length header, 
            // thus browser do not redirect, but shows content (debug) of that length
            ob_start();
            $mailer->SMTPDebug = (int)$this->settings->debugOutputLevel; // Enable debug output
            if ($this->settings->smtp) {
                $mailer->isSMTP();
            } else {
                $mailer->isMail();
            }
            if ($this->settings->disableTls) {
                $mailer->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ];
            }
            $mailer->Host = $this->settings->smtpServer; // Specify main and backup SMTP servers
            $mailer->Port = (int)$this->settings->smtpPort; // TCP port to connect to
            $mailer->SMTPAuth = (bool)$this->settings->smtpAuth; // Enable SMTP authentication
            $mailer->Username = $this->settings->smtpUsername; // SMTP username
            $mailer->Password = $this->settings->smtpPassword; // SMTP password
            $mailer->SMTPSecure = $this->settings->smtpSecure; // Enable TLS encryption, `ssl` or null also accepted
            $mailer->CharSet = 'UTF-8';

            //Recipients
            $mailer->setFrom($event->emailFrom, $event->emailFromName);
            if ($event->emailBccFrom !== null) {
                $mailer->addCC($event->emailBccFrom, $event->emailFromName);
            }

            if ($this->settings->sendMailToMainRecipient) {
                $mailer->addAddress($recipientEmail);
            }

            // Content
            $mailer->isHTML();
            $mailer->Subject = $event->readableName . ' - ' . $subject;
            $mailer->Body = $messageBody;
            $mailer->AltBody = strip_tags($messageBody);

            $mailer->send();
        } catch (\Exception $e) {
            throw new Exception('Error sending email', $e->getCode(), $e);
        } finally {
            ob_get_clean();
        }
    }
}
