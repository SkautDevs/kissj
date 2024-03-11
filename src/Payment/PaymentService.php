<?php

declare(strict_types=1);

namespace kissj\Payment;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use h4kuna\Fio\Exceptions\ServiceUnavailable;
use kissj\Application\DateTimeUtils;
use kissj\BankPayment\BankPayment;
use kissj\BankPayment\BankPaymentRepository;
use kissj\BankPayment\BankServiceProvider;
use kissj\Event\Event;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Logging\Sentry\SentryCollector;
use kissj\Mailer\Mailer;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Troop\TroopLeader;
use kissj\User\UserService;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PaymentService
{
    public function __construct(
        private readonly BankServiceProvider $bankServiceProvider,
        private readonly BankPaymentRepository $bankPaymentRepository,
        private readonly PaymentRepository $paymentRepository,
        private readonly ParticipantRepository $participantRepository,
        private readonly UserService $userService,
        private readonly FlashMessagesBySession $flashMessages,
        private readonly Mailer $mailer,
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface $logger,
        private readonly SentryCollector $sentryCollector,
    ) {
    }

    public function createAndPersistNewPayment(Participant $participant): Payment
    {
        $event = $participant->getUserButNotNull()->event;

        $payment = new Payment();
        $payment->participant = $participant;
        $payment->variableSymbol = $this->getVariableNumber($event);
        $payment->constantSymbol = $event->constantSymbol;
        $payment->price = (string)$event->defaultPrice;
        $payment->currency = $event->currency;
        $payment->status = PaymentStatus::Waiting;
        $payment->purpose = 'event fee';
        $payment->accountNumber = $event->accountNumber;
        $payment->iban = $event->iban;
        $payment->swift = $event->swift;
        $payment->due = $this->calculatePaymentDueDate(DateTimeUtils::getDateTime(), $event->startDay);
        $payment->note = $this->composeNote($participant, $event);

        $payment = $event->getEventType()->transformPayment($payment, $participant);

        $this->paymentRepository->persist($payment);

        return $payment;
    }

    public function cancelPayment(Payment $payment): Payment
    {
        if ($payment->status !== PaymentStatus::Waiting) {
            throw new \RuntimeException('Payment cancellation is allow only for payments with status '
                . PaymentStatus::Waiting->value);
        }

        $payment->status = PaymentStatus::Canceled;
        $this->paymentRepository->persist($payment);

        return $payment;
    }

    public function cancelDuePayments(Event $event, int $limit = 5): void
    {
        $duePayments = $this->paymentRepository->getDuePayments($event);
        $singleDuePayments = array_filter(
            $duePayments,
            fn (Payment $payment) => count($payment->participant->payment) === 1,
        );
        $deniedPaymentsCount = 0;

        foreach (array_slice($singleDuePayments, 0, $limit) as $payment) {
            $this->cancelPayment($payment);

            $this->userService->setUserOpen($payment->participant->getUserButNotNull());
            $this->mailer->sendDuePaymentDenied($payment->participant);
            $this->logger->info('Payment ID ' . $payment->id . ' was automatically denied because payment due');
            $deniedPaymentsCount++;
        }

        $this->flashMessages->info($this->translator->trans('flash.info.duePaymentDenied') . ': ' . $deniedPaymentsCount);
    }

    public function confirmPayment(Payment $payment): Payment
    {
        if ($payment->status !== PaymentStatus::Waiting) {
            throw new \RuntimeException('Payment confirmation is allow only for payments with status '
                . PaymentStatus::Waiting->value);
        }

        $payment->status = PaymentStatus::Paid;
        $this->paymentRepository->persist($payment);

        $now = DateTimeUtils::getDateTime();

        $participant = $payment->participant;
        $this->setParticipantPaidWithTime($participant, $now);

        if ($participant instanceof TroopLeader) {
            foreach ($participant->troopParticipants as $tp) {
                $this->setParticipantPaidWithTime($tp, $now);
            }
        }

        $this->mailer->sendRegistrationPaid($participant);

        return $payment;
    }

    private function setParticipantPaidWithTime(Participant $participant, \DateTimeImmutable $now): void
    {
        $this->userService->setUserPaid($participant->getUserButNotNull());

        $participant->registrationPayDate = $now;
        $this->participantRepository->persist($participant);
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

        if ($freshBankPayments === []) {
            try {
                $newPaymentsCount = $this->bankServiceProvider->provideBankService($event->bankSlug)
                    ->getAndSafeFreshPaymentsFromBank($event);

                if ($newPaymentsCount > 0) {
                    $this->flashMessages->info($this->translator->trans('flash.info.newPayments') . $newPaymentsCount);
                } else {
                    $this->flashMessages->info($this->translator->trans('flash.info.noNewPayments'));
                }
            } catch (ServiceUnavailable $e) {
                $this->sentryCollector->collect($e);
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
                    $this->logger->info('Payment ID ' . $payment->id
                        . ' automatically set to status ' . $payment->status->value);

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

        if ($counterNewPaid > 0) {
            $this->flashMessages->success($this->translator->trans('flash.success.adminPairedPayments') . $counterNewPaid);
        }

        if ($counterUnknownPayment > 0) {
            $this->flashMessages->info($this->translator->trans('flash.info.adminPaymentsUnrecognized') . $counterUnknownPayment);
        }
    }

    private function getVariableNumber(Event $event): string
    {
        do {
            $variableNumber = $this->generateVariableNumber($event->prefixVariableSymbol);
        } while ($this->paymentRepository->isVariableNumberExisting($variableNumber));

        return $variableNumber;
    }

    protected function generateVariableNumber(?int $prefix): string
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

    public function calculatePaymentDueDate(
        DateTimeImmutable $dateFrom,
        DateTimeInterface $eventStartDate,
    ): DateTimeImmutable {
        $datePlus14days = $dateFrom->add(DateInterval::createFromDateString('14 days'));

        return $datePlus14days < $eventStartDate
            ? $datePlus14days
            : DateTimeImmutable::createFromInterface($eventStartDate);
    }

    private function composeNote(Participant $participant, Event $event): string
    {
        if ($participant instanceof PatrolLeader) {
            return $event->slug . ' ' . $participant->patrolName . ' ' . $participant->getFullName();
        }

        return $event->slug . ' ' . $participant->getFullName();
    }

    public function setBankPaymentPaired(int $paymentId): BankPayment
    {
        $bankPayment = $this->bankPaymentRepository->get($paymentId);
        $bankPayment->status = BankPayment::STATUS_PAIRED;
        $this->bankPaymentRepository->persist($bankPayment);

        return $bankPayment;
    }

    public function setBankPaymentUnrelated(int $paymentId): BankPayment
    {
        $bankPayment = $this->bankPaymentRepository->get($paymentId);
        $bankPayment->status = BankPayment::STATUS_UNRELATED;
        $this->bankPaymentRepository->persist($bankPayment);

        return $bankPayment;
    }
}
