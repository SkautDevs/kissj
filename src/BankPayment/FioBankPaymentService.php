<?php

namespace kissj\BankPayment;

use h4kuna\Fio\Exceptions\ServiceUnavailable;
use h4kuna\Fio\FioRead;
use Psr\Log\LoggerInterface;

class FioBankPaymentService implements IBankPaymentService {
    private $bankPaymentRepository;
    private $fioRead;
    private $logger;

    public function __construct(
        BankPaymentRepository $bankPaymentRepository,
        FioRead $fioRead,
        LoggerInterface $logger
    ) {
        $this->bankPaymentRepository = $bankPaymentRepository;
        $this->fioRead = $fioRead;
        $this->logger = $logger;
    }

    public function setBreakpoint(\DateTimeImmutable $dateTime): bool {
        try {
            $this->fioRead->setLastDate($dateTime);
        } catch (ServiceUnavailable $e) {
            $this->logger->error('Setting breakpoint for Fio Bank failed: '.$e->getMessage());

            return false;
        }
        $this->logger->error('Set breakpoint for Fio Bank on time '.$dateTime->format('Y-d-m'));

        return true;
    }

    public function getAndSafeFreshPaymentsFromBank(): int {
        // TODO deduplicate persisted and new ones, possibly by moveId
        $freshPayments = $this->fioRead->lastDownload();
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
        $bankPayment = $this->bankPaymentRepository->find($paymentId);
        $bankPayment->status = BankPayment::STATUS_PAIRED;
        $this->bankPaymentRepository->persist($bankPayment);

        return $bankPayment;
    }

    public function setBankPaymentUnrelated(int $paymentId): BankPayment {
        /** @var BankPayment $bankPayment */
        $bankPayment = $this->bankPaymentRepository->find($paymentId);
        $bankPayment->status = BankPayment::STATUS_UNRELATED;
        $this->bankPaymentRepository->persist($bankPayment);

        return $bankPayment;
    }
}
