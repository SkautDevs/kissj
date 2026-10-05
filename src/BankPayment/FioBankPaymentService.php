<?php

declare(strict_types=1);

namespace kissj\BankPayment;

use h4kuna\Fio\Exceptions\ServiceUnavailable;
use h4kuna\Fio\FioRead;
use h4kuna\Fio\Read\Transaction;
use h4kuna\Fio\Read\TransactionList;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

readonly class FioBankPaymentService implements IBankPaymentService
{
    public function __construct(
        private BankPaymentRepository $bankPaymentRepository,
        private EventRepository $eventRepository,
        private FioBankReaderFactory $fioBankReaderFactory,
        private LoggerInterface $logger,
    ) {
    }

    public function getAndSafeFreshPaymentsFromBank(Event $event): int
    {
        $fioRead = $this->fioBankReaderFactory->getFioRead($event);
        $lastMoveId = $event->bankLastMoveId;
        if ($lastMoveId === null) {
            $this->rewindToLastSavedPayment($fioRead, $event);

            return $this->savePayments($event, $fioRead->lastDownload(), true);
        }

        $freshPayments = $fioRead->lastDownload();
        $idLastDownload = $this->idLastDownload($freshPayments);
        if ($idLastDownload !== $lastMoveId) {
            $this->logger->warning(sprintf(
                'Event ID %d Fio breakpoint drifted: stored %d, bank %s, rewinding',
                $event->id,
                $lastMoveId,
                $idLastDownload === null ? 'null' : (string)$idLastDownload,
            ));
            $savedCount = $this->savePayments($event, $freshPayments, false);
            $this->assertRewindAccepted($fioRead->setLastId($lastMoveId));

            return $savedCount;
        }

        return $this->savePayments($event, $freshPayments, true);
    }

    private function rewindToLastSavedPayment(FioRead $fioRead, Event $event): void
    {
        $lastBankPaymentId = $this->bankPaymentRepository->getLastBankPaymentId($event);
        if ($lastBankPaymentId !== null) {
            $this->assertRewindAccepted($fioRead->setLastId((int)$lastBankPaymentId));
        } else {
            $this->assertRewindAccepted($fioRead->setLastDate('now - 89 days')); // bank allows max 90 days back
        }
    }

    private function assertRewindAccepted(ResponseInterface $response): void
    {
        if ($response->getStatusCode() >= 400) {
            throw new ServiceUnavailable(sprintf(
                'Fio breakpoint rewind failed: %d %s',
                $response->getStatusCode(),
                $response->getReasonPhrase(),
            ));
        }
    }

    private function savePayments(Event $event, TransactionList $freshPayments, bool $moveCursor): int
    {
        return $this->bankPaymentRepository->transactional(function () use ($event, $freshPayments, $moveCursor): int {
            $incomes = [];
            foreach ($freshPayments as $freshPayment) {
                /** @var Transaction $freshPayment */
                if ($freshPayment->amount > 0) { // get only incomes
                    $incomes[] = $freshPayment;
                }
            }

            $knownBankIds = $this->bankPaymentRepository->findExistingBankIds(
                $event,
                array_map(fn (Transaction $income): string => (string)$income->moveId, $incomes),
            );

            $savedBankPaymentsCount = 0;
            foreach ($incomes as $income) {
                if (in_array((string)$income->moveId, $knownBankIds, true)) {
                    continue;
                }
                $bankPayment = new BankPayment();
                $bankPayment->mapTransactionInto($income, $event);
                $this->bankPaymentRepository->persist($bankPayment);
                $savedBankPaymentsCount++;
            }

            if ($moveCursor) {
                $newMoveId = $this->idTo($freshPayments) ?? $this->idLastDownload($freshPayments);
                if ($newMoveId !== null) {
                    $event->bankLastMoveId = $newMoveId;
                    $this->eventRepository->persist($event);
                }
            }

            return $savedBankPaymentsCount;
        });
    }

    private function idLastDownload(TransactionList $transactionList): ?int
    {
        $idLastDownload = $transactionList->getInfo()->idLastDownload ?? null;

        return is_int($idLastDownload) ? $idLastDownload : null;
    }

    private function idTo(TransactionList $transactionList): ?int
    {
        $idTo = $transactionList->getInfo()->idTo ?? null;

        return is_int($idTo) ? $idTo : null;
    }
}
