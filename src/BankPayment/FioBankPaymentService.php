<?php declare(strict_types=1);

namespace kissj\BankPayment;

use h4kuna\Fio\Exceptions\ServiceUnavailable;
use kissj\Event\Event;
use Psr\Log\LoggerInterface;

class FioBankPaymentService implements IBankPaymentService {
    public function __construct(
        private BankPaymentRepository $bankPaymentRepository,
        private FioBankReaderFactory $fioBankReaderFactory,
        private LoggerInterface $logger,
    ) {
    }

    public function setBreakpoint(\DateTimeImmutable $dateTime, Event $event): bool {
        try {
            $this->fioBankReaderFactory->getFioRead($event)->setLastDate($dateTime);
        } catch (ServiceUnavailable $e) {
            $this->logger->error('Setting breakpoint for Fio Bank failed: '.$e->getMessage());

            return false;
        }
        $this->logger->info('Set breakpoint for Fio Bank on time '.$dateTime->format('Y-d-m'));

        return true;
    }

    public function getAndSafeFreshPaymentsFromBank(Event $event): int {
        // TODO deduplicate persisted and new ones, possibly by moveId
        $freshPayments = $this->fioBankReaderFactory->getFioRead($event)->lastDownload();
        foreach ($freshPayments as $freshPayment) {
            if ($freshPayment->volume > 0) { // get only incomes
                $bankPayment = new BankPayment();
                $bankPayment->mapTransactionInto($freshPayment);
                // TODO optimalize
                $this->bankPaymentRepository->persist($bankPayment);
            }
        }

        return count($freshPayments);
    }

    public function setBankPaymentPaired(int $paymentId): BankPayment {
        /** @var BankPayment $bankPayment */
        $bankPayment = $this->bankPaymentRepository->get($paymentId);
        $bankPayment->status = BankPayment::STATUS_PAIRED;
        $this->bankPaymentRepository->persist($bankPayment);

        return $bankPayment;
    }

    public function setBankPaymentUnrelated(int $paymentId): BankPayment {
        /** @var BankPayment $bankPayment */
        $bankPayment = $this->bankPaymentRepository->get($paymentId);
        $bankPayment->status = BankPayment::STATUS_UNRELATED;
        $this->bankPaymentRepository->persist($bankPayment);

        return $bankPayment;
    }
}
