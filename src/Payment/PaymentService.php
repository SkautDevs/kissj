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
use kissj\Logging\Sentry\SentryCollector;
use kissj\Mailer\Mailer;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Troop\TroopLeader;
use kissj\User\UserService;
use Psr\Log\LoggerInterface;

class PaymentService
{
    public function __construct(
        private readonly BankServiceProvider $bankServiceProvider,
        private readonly BankPaymentRepository $bankPaymentRepository,
        private readonly PaymentRepository $paymentRepository,
        private readonly ParticipantRepository $participantRepository,
        private readonly UserService $userService,
        private readonly Mailer $mailer,
        private readonly LoggerInterface $logger,
        private readonly SentryCollector $sentryCollector,
    ) {
    }

    public function createAndPersistNewEventPayment(Participant $participant): Payment
    {
        $event = $participant->getUserButNotNull()->event;

        $payment =  $this->createNewPayment($participant, $event, $event->defaultPrice);
        $payment = $event->getEventType()->transformPaymentPrice($payment, $participant);
        $this->paymentRepository->persist($payment);

        return $payment;
    }

    public function createAndPersistNewCustomPayment(Participant $participant, int $price, string $purpouse): Payment
    {
        $event = $participant->getUserButNotNull()->event;
        $payment =  $this->createNewPayment($participant, $event, $price, $purpouse);

        $this->paymentRepository->persist($payment);

        return $payment;
    }

    private function createNewPayment(
        Participant $participant,
        Event $event,
        int $price,
        string $purpouse = 'event fee',
    ): Payment {
        $payment = new Payment();
        $payment->participant = $participant;
        $payment->variableSymbol = $this->getNewVariableNumber($event);
        $payment->constantSymbol = $event->constantSymbol;
        $payment->price = (string)$price;
        $payment->currency = $event->currency;
        $payment->status = PaymentStatus::Waiting;
        $payment->purpose = $purpouse;
        $payment->accountNumber = $event->accountNumber;
        $payment->iban = $event->iban;
        $payment->swift = $event->swift;
        $payment->due = $this->calculatePaymentDueDate(DateTimeUtils::getDateTime(), $event->startDay);
        $payment->note = $this->composeNote($participant, $event);

        return $payment;
    }

    public function cancelPayment(Payment $payment): PaymentResult
    {
        if ($payment->status === PaymentStatus::Canceled) {
            return PaymentResult::warning($payment, 'flash.warning.paymentAlreadyCanceled');
        }

        if ($payment->status !== PaymentStatus::Waiting) {
            throw new \RuntimeException('Payment cancellation is allow only for payments with status '
                . PaymentStatus::Waiting->value);
        }

        $payment->status = PaymentStatus::Canceled;
        $this->paymentRepository->persist($payment);

        return new PaymentResult($payment);
    }

    public function cancelDuePayments(Event $event, int $limit = 5): PaymentResult
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

        return PaymentResult::withMessages([
            new PaymentResultMessage(
                PaymentMessageSeverity::Info,
                'flash.info.duePaymentDenied',
                ['%count%' => (string)$deniedPaymentsCount],
            ),
        ]);
    }

    public function confirmPayment(Payment $payment): PaymentResult
    {
        if ($payment->status === PaymentStatus::Paid) {
            return PaymentResult::warning($payment, 'flash.warning.paymentAlreadyPaid');
        }

        if ($payment->status !== PaymentStatus::Waiting) {
            throw new \RuntimeException('Payment confirmation is allow only for payments with status '
                . PaymentStatus::Waiting->value);
        }

        $now = DateTimeUtils::getDateTime();

        $payment->status = PaymentStatus::Paid;
        $payment->paidAt = $now;
        $this->paymentRepository->persist($payment);

        $participant = $payment->participant;
        if ($participant->countWaitingPayments() > 1) {
            // one payment was just marked as paid, but not propagated to the participant entity yet
            $this->mailer->sendPaidPartially($participant);

            return new PaymentResult($payment);
        }

        $this->setParticipantPaidWithTime($participant, $now);

        if ($participant instanceof TroopLeader) {
            foreach ($participant->troopParticipants as $tp) {
                $this->setParticipantPaidWithTime($tp, $now);
            }
        }

        $this->mailer->sendRegistrationPaid($participant);

        return PaymentResult::success($payment, 'flash.success.confirmPayment');
    }

    private function setParticipantPaidWithTime(Participant $participant, DateTimeImmutable $now): void
    {
        $this->userService->setUserPaid($participant->getUserButNotNull());

        $participant->registrationPayDate = $now;
        $this->participantRepository->persist($participant);
    }

    /**
     * plan - firstly it looks, if they are any payments downloaded from bank to pair with our generated payments
     * if not, download fresh data from bank and then vvv
     * pair few of them (few because of mailing and processing time)
     */
    public function updatePayments(Event $event, int $limit = 10): PaymentResult
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
                    return PaymentResult::withMessages([
                        new PaymentResultMessage(
                            PaymentMessageSeverity::Info,
                            'flash.info.newPayments',
                            ['%count%' => (string)$newPaymentsCount],
                        ),
                    ]);
                }

                return PaymentResult::withMessages([
                    new PaymentResultMessage(PaymentMessageSeverity::Info, 'flash.info.noNewPayments'),
                ]);
            } catch (ServiceUnavailable $e) {
                $this->sentryCollector->collect($e);
                $this->logger->info('Event ID ' . $event->id . ' failed to fetch data from bank: ' . $e->getMessage());

                return PaymentResult::withMessages([
                    new PaymentResultMessage(PaymentMessageSeverity::Error, 'flash.error.fioConnectionFailed'),
                ]);
            }
        }

        // TODO make more atomic - set "processing" status or something
        $participantKeydPayments = $this->paymentRepository->getWaitingPaymentsKeydByVariableSymbols($event);
        $counterNewPaid = 0;
        $counterUnknownPayment = 0;

        /** @var BankPayment $bankPayment */
        foreach (array_slice($freshBankPayments, 0, $limit) as $bankPayment) {
            if (array_key_exists($bankPayment->variableSymbol ?? '', $participantKeydPayments)) {
                $payment = $participantKeydPayments[$bankPayment->variableSymbol ?? ''];
                if ($payment->price === $bankPayment->price) {
                    // match!
                    $this->confirmPayment($payment);
                    $this->logger->info('Payment ID ' . $payment->id
                        . ' automatically set to status ' . $payment->status->value);

                    $bankPayment->status = BankPayment::STATUS_PAIRED;
                    $counterNewPaid++;
                } else {
                    // matching VS, not matching price
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

        $messages = [];
        if ($counterNewPaid > 0) {
            $messages[] = new PaymentResultMessage(
                PaymentMessageSeverity::Success,
                'flash.success.adminPairedPayments',
                ['%count%' => (string)$counterNewPaid],
            );
        }

        if ($counterUnknownPayment > 0) {
            $messages[] = new PaymentResultMessage(
                PaymentMessageSeverity::Info,
                'flash.info.adminPaymentsUnrecognized',
                ['%count%' => (string)$counterUnknownPayment],
            );
        }

        return PaymentResult::withMessages($messages);
    }

    public function getNewVariableNumber(Event $event): string
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
            return $event->slug . ' ' . ($participant->patrolName ?? '') . ' ' . $participant->getFullName();
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
