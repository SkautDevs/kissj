<?php

declare(strict_types=1);

namespace kissj\BankPayment;

use h4kuna\Fio\Exceptions\ServiceUnavailable;
use h4kuna\Fio\Response\Read\Transaction;
use kissj\Event\Event;
use kissj\Logging\Sentry\SentryCollector;
use Psr\Log\LoggerInterface;

class FioBankPaymentService implements IBankPaymentService
{
    public function __construct(
        private readonly BankPaymentRepository $bankPaymentRepository,
        private readonly FioBankReaderFactory $fioBankReaderFactory,
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
}
