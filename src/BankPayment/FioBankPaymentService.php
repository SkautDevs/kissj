<?php

declare(strict_types=1);

namespace kissj\BankPayment;

use h4kuna\Fio\Exceptions\ServiceUnavailable;
use h4kuna\Fio\Response\Read\Transaction;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use Psr\Log\LoggerInterface;

class FioBankPaymentService implements IBankPaymentService
{
    public function __construct(
        private EventRepository $eventRepository,
        private BankPaymentRepository $bankPaymentRepository,
        private FioBankReaderFactory $fioBankReaderFactory,
        private LoggerInterface $logger,
    ) {
    }

    public function setBreakpoint(\DateTimeImmutable $dateTime, Event $event): bool
    {
        try {
            $this->fioBankReaderFactory->getFioRead($event)->setLastDate($dateTime);
        } catch (ServiceUnavailable $e) {
            $this->logger->error('Setting breakpoint for Fio Bank failed: '.$e->getMessage());

            return false;
        }
        $this->logger->info('Set breakpoint for Fio Bank on time '.$dateTime->format('Y-d-m'));

        return true;
    }

    public function getAndSafeFreshPaymentsFromBank(Event $event): int
    {
        $now = new \DateTimeImmutable();

        /** @var Transaction[] $freshPayments */
        $freshPayments = $this->fioBankReaderFactory->getFioRead($event)->movements($event->bankBreakpoint, $now);

        $this->setBreakpoint($event->bankBreakpoint, $event);
        $event->bankBreakpoint = $now;
        $this->eventRepository->persist($event);

        $savedBankPaymentsCount = 0;
        foreach ($freshPayments as $freshPayment) {
            if ($freshPayment->volume > 0) { // get only incomes
                $bankPayment = new BankPayment();
                $bankPayment = $bankPayment->mapTransactionInto($freshPayment, $event);
                // TODO optimalize
                $this->bankPaymentRepository->persist($bankPayment);
                $savedBankPaymentsCount++;
            }
        }

        return $savedBankPaymentsCount;
    }

    public function setBankPaymentPaired(int $paymentId): BankPayment
    {
        /** @var BankPayment $bankPayment */
        $bankPayment = $this->bankPaymentRepository->get($paymentId);
        $bankPayment->status = BankPayment::STATUS_PAIRED;
        $this->bankPaymentRepository->persist($bankPayment);

        return $bankPayment;
    }

    public function setBankPaymentUnrelated(int $paymentId): BankPayment
    {
        /** @var BankPayment $bankPayment */
        $bankPayment = $this->bankPaymentRepository->get($paymentId);
        $bankPayment->status = BankPayment::STATUS_UNRELATED;
        $this->bankPaymentRepository->persist($bankPayment);

        return $bankPayment;
    }
}
