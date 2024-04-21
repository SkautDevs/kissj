<?php

declare(strict_types=1);

namespace kissj\Payment;

use Dibi\Row;
use kissj\Event\Event;
use kissj\Orm\Repository;
use RuntimeException;

class PaymentRepository extends Repository
{
    public function getById(int $paymentId, Event $event): Payment
    {
        $qb = $this->createFluent();
        $qb->join('participant')->as('participant')->on('participant.id = payment.participant_id');
        $qb->join('user')->as('u')->on('u.id = participant.user_id');
        $qb->where('u.event_id = %i', $event->id);
        $qb->where('payment.id = %i', $paymentId);

        /** @var ?Row $row */
        $row = $qb->fetch();
        if ($row === null) {
            throw new RuntimeException(sprintf('Payment with ID %s not found', $paymentId));
        }

        /** @var Payment $payment */
        $payment = $this->createEntity($row);

        return $payment;
    }

    public function isVariableNumberExisting(string $variableNumber): bool
    {
        return $this->isExisting(['variable_symbol' => $variableNumber]);
    }

    /**
     * @return Payment[]
     */
    public function getWaitingPaymentsKeydByVariableSymbols(Event $event): array
    {
        $waitingEventPayments = $this->getEventPayments($event);

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
        $waitingEventPayments = $this->getEventPayments($event);

        return array_filter(
            $waitingEventPayments,
            fn (Payment $payment) => $payment->isPaymentOverdue(),
        );
    }

    /**
     * @return Payment[]
     */
    private function getEventPayments(Event $event): array
    {
        $qb = $this->createFluent();
        $qb->join('participant')->as('participant')->on('participant.id = payment.participant_id');
        $qb->join('user')->as('u')->on('u.id = participant.user_id');
        $qb->where('u.event_id = %i', $event->id);
        $qb->where('payment.status = %s', PaymentStatus::Waiting);

        /** @var Payment[] $waitingEventPayments */
        $waitingEventPayments = $this->createEntities($qb->fetchAll());

        return $waitingEventPayments;
    }
}
