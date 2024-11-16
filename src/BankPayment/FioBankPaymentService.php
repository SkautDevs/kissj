<?php

declare(strict_types=1);

namespace kissj\BankPayment;

use h4kuna\Fio\Read\Transaction;
use kissj\Event\Event;

readonly class FioBankPaymentService implements IBankPaymentService
{
    public function __construct(
        private BankPaymentRepository $bankPaymentRepository,
        private FioBankReaderFactory $fioBankReaderFactory,
    ) {
    }

    public function getAndSafeFreshPaymentsFromBank(Event $event): int
    {
        $fioRead = $this->fioBankReaderFactory->getFioRead($event);
        $lastBankPaymentId = $this->bankPaymentRepository->getLastBankPaymentId($event);
        if ($lastBankPaymentId !== null) {
            $fioRead->setLastId((int)$lastBankPaymentId);
        }

        /** @var Transaction[] $freshPayments */
        $freshPayments = $fioRead->lastDownload();

        $savedBankPaymentsCount = 0;
        foreach ($freshPayments as $freshPayment) {
            if ($freshPayment->amount > 0) { // get only incomes
                $bankPayment = new BankPayment();
                $bankPayment = $bankPayment->mapTransactionInto($freshPayment, $event);
                // TODO optimize
                $this->bankPaymentRepository->persist($bankPayment);
                $savedBankPaymentsCount++;
            }
        }

        return $savedBankPaymentsCount;
    }
}
