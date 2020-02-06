<?php

namespace kissj\PaymentImport;

class Payment {
    public $event;
    /** @var string */
    public $variableSymbol;
    /** @var int */
    public $senderName;
    /** @var string */
    public $senderAccountNr;
    /** @var string */
    public $amount;
    /** @var float */
    public $currency;
    /** @var string */
    public $noteForReceiver;
    /** @var string */
    public $dateReceived;
    /** @var \DateTimeImmutable */
}
