<?php

declare(strict_types=1);

namespace kissj\Payment;

use h4kuna\Fio\Exceptions\ServiceUnavailable;
use kissj\BankPayment\BankPayment;
use kissj\BankPayment\BankPaymentRepository;
use kissj\BankPayment\FioBankPaymentService;
use kissj\Event\Event;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Mailer\PhpMailerWrapper;
use kissj\Participant\Participant;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\User\UserService;
use Monolog\Logger;
use Symfony\Contracts\Translation\TranslatorInterface;

class PaymentService
{
    public function __construct(
        private FioBankPaymentService $bankPaymentService,
        private BankPaymentRepository $bankPaymentRepository,
        private PaymentRepository $paymentRepository,
        private UserService $userService,
        private FlashMessagesBySession $flashMessages,
        private PhpMailerWrapper $mailer,
        private TranslatorInterface $translator,
        private Logger $logger,
    ) {
    }

    public function createAndPersistNewPayment(Participant $participant): Payment
    {
        $event = $participant->getUserButNotNull()->event;
        do {
            $variableNumber = $this->getVariableNumber($event->prefixVariableSymbol);
        } while ($this->paymentRepository->isVariableNumberExisting($variableNumber));

        $payment = new Payment();
        $payment->participant = $participant;
        $payment->variableSymbol = $variableNumber;
        $payment->price = (string)$event->getEventType()->getPrice($participant);
        $payment->currency = $event->currency;
        $payment->status = Payment::STATUS_WAITING;
        $payment->purpose = 'event fee';
        $payment->accountNumber = $event->accountNumber;
        $payment->iban = $event->iban;
        $payment->due = $event->getEventType()->calculatePaymentDueDate(new \DateTimeImmutable('now'));
        if ($participant instanceof PatrolLeader) {
            $payment->note = $event->slug . ' ' . $participant->patrolName . ' ' . $participant->getFullName();
        } else {
            $payment->note = $event->slug . ' ' . $participant->getFullName();
        }

        $this->paymentRepository->persist($payment);

        return $payment;
    }

    public function cancelPayment(Payment $payment): Payment
    {
        if ($payment->status !== Payment::STATUS_WAITING) {
            throw new \RuntimeException('Payment cancellation is allow only for payments with status "'
                . Payment::STATUS_WAITING . '"');
        }

        $payment->status = Payment::STATUS_CANCELED;
        $this->paymentRepository->persist($payment);

        return $payment;
    }

    public function cancelDuePayments(Event $event, int $limit = 5): void
    {
        $duePayments = $this->paymentRepository->getDuePayments($event);
        $singleDuePayments = array_filter(
            $duePayments,
            fn(Payment $payment) => count($payment->participant->payment) === 1,
        );
        $deniedPaymentsCount = 0;

        foreach (array_slice($singleDuePayments, 0, $limit) as $payment) {
            $this->cancelPayment($payment);

            $this->userService->openRegistration($payment->participant->getUserButNotNull());
            $this->mailer->sendDuePaymentDenied($payment->participant);
            $this->logger->info('Payment ID ' . $payment->id . ' was automatically denied because payment due');
            $deniedPaymentsCount++;
        }

        $this->flashMessages->info($this->translator->trans('flash.info.duePaymentDenied') . ': ' . $deniedPaymentsCount);
    }

    public function confirmPayment(Payment $payment): Payment
    {
        if ($payment->status !== Payment::STATUS_WAITING) {
            throw new \RuntimeException('Payment confirmation is allow only for payments with status "'
                . Payment::STATUS_WAITING . '"');
        }

        $this->userService->payRegistration($payment->participant->getUserButNotNull());
        $payment->status = Payment::STATUS_PAID;
        $this->paymentRepository->persist($payment);
        $this->mailer->sendRegistrationPaid($payment->participant);

        return $payment;
    }

    /**
     * plan - frstly it looks, if they are any payments downloaded from bank to pair with our generated payments
     * if not, download fresh data from bank and then vvv
     * pair few of them (few because of mailing and processing time)
     */
    public function updatePayments(Event $event, int $limit = 10): void
    {
        $freshBankPayments = $this->bankPaymentRepository->getBankPaymentsOrderedWithStatus(
            $event,
            BankPayment::STATUS_FRESH,
        );

        if (count($freshBankPayments) === 0) {
            try {
                $newPaymentsCount = $this->bankPaymentService->getAndSafeFreshPaymentsFromBank($event);

                if ($newPaymentsCount > 0) {
                    $this->flashMessages->info($this->translator->trans('flash.info.newPayments') . $newPaymentsCount);
                } else {
                    $this->flashMessages->info($this->translator->trans('flash.info.noNewPayments'));
                }
            } catch (ServiceUnavailable $e) {
                $this->flashMessages->error($this->translator->trans('flash.error.fioConnectionFailed'));
                $this->logger->info('Event ID ' . $event->id . ' failed to fetch data from bank: ' . $e->getMessage());
            }

            return;
        }

        // TODO make more atomic - set "processing" status or something
        $participantKeydPayments = $this->paymentRepository->getWaitingPaymentsKeydByVariableSymbols($event);
        $counterNewPaid = 0;
        $counterUnknownPayment = 0;

        /** @var BankPayment $bankPayment */
        foreach (array_slice($freshBankPayments, 0, $limit) as $bankPayment) {
            if (array_key_exists($bankPayment->variableSymbol ?? '', $participantKeydPayments)) {
                $payment = $participantKeydPayments[$bankPayment->variableSymbol];
                if ($payment->price === $bankPayment->price) {
                    // match!
                    $this->confirmPayment($payment);
                    $this->logger->info('Payment ID ' . $payment->id . ' automatically set to status ' . $payment->status);

                    $bankPayment->status = BankPayment::STATUS_PAIRED;
                    $counterNewPaid++;
                } else {
                    // matching VS, not matchnig price
                    $bankPayment->status = BankPayment::STATUS_UNKNOWN;
                    $counterUnknownPayment++;
                }
            } else {
                // found no payment of this VS
                $bankPayment->status = BankPayment::STATUS_UNKNOWN;
                $counterUnknownPayment++;
            }

            $this->bankPaymentRepository->persist($bankPayment);
        }

        if ($counterNewPaid) {
            $this->flashMessages->success($this->translator->trans('flash.success.adminPairedPayments') . $counterNewPaid);
        }

        if ($counterUnknownPayment) {
            $this->flashMessages->info($this->translator->trans('flash.info.adminPaymentsUnrecognized') . $counterUnknownPayment);
        }
    }

    protected function getVariableNumber(?int $prefix): string
    {
        if ($prefix === null) {
            return str_pad((string)random_int(0, 9_999_999_999), 10, '0', STR_PAD_LEFT);
        }

        $prefixLength = strlen((string)$prefix);
        if ($prefixLength > 5) {
            throw new \RuntimeException('prefix is too long: ' . $prefix);
        }

        $variableNumber = (string)$prefix;
        for ($i = 0; $i < 10 - $prefixLength; $i++) {
            $variableNumber .= random_int(0, 9);
        }

        return $variableNumber;
    }

    # Jak vygenerovat hezci CSV z Money S3
    /* cat Seznam\ bankovních\ dokladů_04122017_pok.csv | grep "^Detail 1;0" | head -n1 > test.csv; cat Seznam\ bankovních\ dokladů_04122017_pok.csv | grep "^Detail 1;1" >> test.csv */
}
