<?php

namespace kissj\Participant\Admin;

use kissj\Participant\Participant;
use kissj\Payment\PaymentStatus;
use kissj\User\UserStatus;

class StatisticValueObject
{
    protected int $openCount;
    protected int $closedCount;
    protected int $approvedCount;
    protected int $afterPayment;
    protected int $paidCount;

    /**
     * @param Participant[] $participants
     */
    public function __construct(array $participants)
    {
        $this->openCount = 0;
        $this->closedCount = 0;
        $this->approvedCount = 0;
        $this->afterPayment = 0;
        $this->paidCount = 0;

        foreach ($participants as $participant) {
            switch ($participant->getUserButNotNull()->getStatus()) {
                case UserStatus::Open:
                    $this->openCount++;
                    break;

                case UserStatus::Closed:
                    $this->closedCount++;
                    break;

                case UserStatus::Approved:
                    $this->approvedCount++;

                    foreach ($participant->getPayments() as $payment) {
                        if ($payment->status !== PaymentStatus::Canceled &&
                            $payment->isPaymentOverdue()
                        ) {
                            $this->afterPayment++;
                            // only one waiting payment is sufficient
                            break;
                        }
                    }
                    break;

                case UserStatus::Paid:
                    $this->paidCount++;
                    break;
                case UserStatus::WithoutRole:
                case UserStatus::Cancelled:
                    break;
            }
        }
    }

    public function getOpenCount(): int
    {
        return $this->openCount;
    }

    public function getClosedCount(): int
    {
        return $this->closedCount;
    }

    public function getApprovedCount(): int
    {
        return $this->approvedCount;
    }

    public function getAfterPayment(): int
    {
        return $this->afterPayment;
    }

    public function getPaidCount(): int
    {
        return $this->paidCount;
    }
}
