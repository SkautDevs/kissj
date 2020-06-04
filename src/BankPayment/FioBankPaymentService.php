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

        return true;
    }

    public function getAndSafeFreshPaymentsFromBank(): void {
        // TODO deduplicate persisted and new ones, possibly by moveId
        $freshPayments = $this->fioRead->lastDownload();
        foreach ($freshPayments as $freshPayment) {
            $bankPayment = new BankPayment();
            $bankPayment->mapTransactionInto($freshPayment);
            // TODO optimalize
            $this->bankPaymentRepository->persist($bankPayment);
        }
    }
}
