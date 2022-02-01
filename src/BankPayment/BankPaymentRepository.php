<?php declare(strict_types=1);

namespace kissj\BankPayment;

use kissj\Event\Event;
use kissj\Orm\Repository;

/**
 * @method BankPayment[] findAll()
 * @method BankPayment[] findBy(mixed[] $criteria, mixed[] $orderBy = [])
 * @method BankPayment|null findOneBy(mixed[] $criteria)
 * @method BankPayment getOneBy(mixed[] $criteria)
 */
class BankPaymentRepository extends Repository
{
    /**
     * @param Event $event
     * @return BankPayment[]
     */
    public function getAllBankPaymentsOrdered(Event $event): array
    {
        return $this->findBy(
            [
                'event' => $event,
            ],
            ['id' => false],
        );
    }

    /**
     * @param Event $event
     * @param string $status
     * @return BankPayment[]
     */
    public function getBankPaymentsOrderedWithStatus(Event $event, string $status): array
    {
        return $this->findBy(
            [
                'event' => $event,
                'status' => $status,
            ],
            ['id' => false],
        );
    }
}
