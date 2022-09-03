<?php

declare(strict_types=1);

namespace kissj\Mailer;

use kissj\Participant\Participant;
use kissj\Payment\Payment;
use kissj\Payment\QrCodeService;
use kissj\User\User;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;
use Symfony\Bridge\Twig\Mime\BodyRenderer;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Mailer\EventListener\MessageListener;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class PhpMailerWrapper
{
    public function __construct(
        private Twig $renderer,
        private MailerSettings $settings,
        private QrCodeService $qrCodeService,
        private TranslatorInterface $translator,
        private LoggerInterface $logger,
    ) {
    }

    public function sendLoginToken(User $user, string $link): void
    {
        $this->sendMailFromTemplate(
            $user->email,
            $this->translator->trans('email.login-token.subject'),
            'login-token',
            [
                'link' => $link,
            ],
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
            [
                'reason' => $reason,
            ],
        );
    }

    public function sendRegistrationApprovedWithPayment(Participant $participant, Payment $payment): void
    {
        $qrCode = fopen(
            $this->qrCodeService->generateQrBase64FromString($payment->getQrPaymentString()),
            'rb',
        );
        $embeds = [];
        if ($qrCode !== false) {
            $embeds = [
                'qr_payment' => $qrCode,
            ];
        }
        $user = $participant->getUserButNotNull();

        $this->sendMailFromTemplate(
            $user->email,
            $this->translator->trans('email.payment-info.subject'),
            'payment-info',
            [
                'participant' => $participant,
                'payment' => $payment,
            ],
            $embeds,
        );
    }

    public function sendRegistrationApprovedForForeignContingents(Participant $participant): void
    {
        $user = $participant->getUserButNotNull();
        $this->sendMailFromTemplate(
            $user->email,
            $this->translator->trans('email.payment-info.subject'),
            'payment-info-contingents',
            [],
        );
    }

    public function sendCancelledPayment(Participant $participant, string $reason): void
    {
        $user = $participant->getUserButNotNull();
        $this->sendMailFromTemplate(
            $user->email,
            $this->translator->trans('email.cancel-payment.subject'),
            'cancel-payment',
            [
                'reason' => $reason,
            ],
        );
    }

    public function sendRegistrationPaid(Participant $participant): void
    {
        $user = $participant->getUserButNotNull();
        $this->sendMailFromTemplate(
            $user->email,
            $this->translator->trans('email.payment-successful.subject'),
            'payment-successful',
            [
                'base64qr' => $this->qrCodeService->generateQrBase64FromString(
                    $participant->getQrParticipantInfoString(),
                ),
            ],
        );
    }

    public function sendGuestRegistrationFinished(Participant $participant): void
    {
        $user = $participant->getUserButNotNull();
        $this->sendMailFromTemplate(
            $user->email,
            $this->translator->trans('email.finished.subject'),
            'finished',
            [
                'participant' => $participant,
            ],
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
            [
                'reason' => 'neobdrželi jsme tvou platbu v termínu pro zaplacení',
            ],
        );
    }

    /**
     * @param array<string, mixed>    $parameters
     * @param array<string, resource> $embeds
     * @param array<string, string>   $attachments
     */
    private function sendMailFromTemplate(
        string $recipientEmail,
        string $subject,
        string $templateName,
        array $parameters,
        array $embeds = [],
        array $attachments = [],
    ): void {
        $event = $this->settings->getEvent();

        $email = new TemplatedEmail();
        $email->from(new Address($event->emailFrom, $event->emailFromName));
        if ($this->settings->sendMailToMainRecipient) {
            $email->to(new Address($recipientEmail));
        }
        if ($event->emailBccFrom !== null) {
            $email->bcc(new Address($event->emailBccFrom, $event->emailFromName));
        }
        $email->subject($event->readableName . ' - ' . $subject);
        $email->htmlTemplate('emails/' . $templateName . '.twig');
        $email->context(array_merge($parameters, ['fullRegistrationLink' => $this->settings->getFullUrlLink()]));
        array_map(fn(string $attachment) => $email->attach($attachment), $attachments);
        foreach ($embeds as $name => $resource) {
            $email->embed($resource, $name);
        }

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(
            new MessageListener(renderer: new BodyRenderer($this->renderer->getEnvironment())),
        );

        $transport = Transport::fromDsn($this->settings->mailDsn, $eventDispatcher, logger: $this->logger);
        $mailer = new Mailer($transport, dispatcher: $eventDispatcher);

        $mailer->send($email);
    }
}
