<?php declare(strict_types=1);

namespace kissj\Payment;

use kissj\Event\Event;
use kissj\Orm\Repository;

/**
 * @method Payment[] findAll()
 * @method Payment[] findBy(mixed[] $criteria)
 * @method Payment|null findOneBy(mixed[] $criteria)
 * @method Payment get(int $paymentId)
 * @method Payment getOneBy(mixed[] $criteria)
 */
class PaymentRepository extends Repository
{
    public function isVariableNumberExisting(string $variableNumber): bool
    {
        return $this->isExisting(['variable_symbol' => $variableNumber]);
    }

    /**
     * @param Event $event
     * @return Payment[]
     */
    public function getWaitingPaymentsKeydByVariableSymbols(Event $event): array
    {
        $waitingPayments = $this->findBy(['status' => Payment::STATUS_WAITING]);
        $waitingEventPayments = $this->filterOnlyEventPayments($waitingPayments, $event);

        $finalPayments = [];
        foreach ($waitingEventPayments as $payment) {
            if (array_key_exists($payment->variableSymbol, $finalPayments)) {
                throw new \RuntimeException(
                    'More payments with same variable symbol existing: ' . $payment->variableSymbol
                );
            }
            $finalPayments[$payment->variableSymbol] = $payment;
        }

        return $finalPayments;
    }

    /**
     * @return Payment[]
     */
    public function getDuePayments(Event $event): array
    {
        $waitingPayments = $this->findBy(['status' => Payment::STATUS_WAITING]);
        $waitingEventPayments = $this->filterOnlyEventPayments($waitingPayments, $event);

        return array_filter(
            $waitingEventPayments,
            fn(Payment $payment) => $payment->getElapsedPaymentDays() > $payment->getMaxElapsedPaymentDays()
        );
    }

    /**
     * @param Payment[] $waitingPayments
     * @param Event $event
     * @return Payment[]
     */
    private function filterOnlyEventPayments(array $waitingPayments, Event $event): array
    {
        return array_filter(
            $waitingPayments,
            fn(Payment $payment) => $payment->participant->getUserButNotNull()->event->id === $event->id
        );
    }
}
