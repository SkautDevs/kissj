<?php

namespace kissj\Payment;

use kissj\Orm\EntityDatetime;
use kissj\Participant\Participant;


/**
 * @property int         $id
 * @property string      $variableSymbol
 * @property string      $price
 * @property string      $currency
 * @property string      $status
 * @property string      $purpose
 * @property string      $accountNumber
 * @property string      $note
 * @property Participant $participant m:hasOne
 */
class Payment extends EntityDatetime {
    public const STATUS_WAITING = 'waiting';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELED = 'canceled';

    public function getElapsedPaymentDays(): int {
        /** @var $createdAt \DateTime */
        $createdAt = $this->createdAt;

        return $createdAt->diff(new \DateTime('now'))->days;
    }

    public function getMaxElapsedPaymentDays(): int {
        return 14; // TODO move into db
    }

    public function getPaymentUntil(): \DateTime {
        /** @var $createdAt \DateTime */
        $createdAt = $this->createdAt;
        $dateInterval = new \DateInterval('P'.$this->getMaxElapsedPaymentDays().'D');

        return $createdAt->add($dateInterval);
    }
}

/**
 * TODO do not forget add note and rename conventions into new DB
 */
