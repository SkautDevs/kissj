<?php declare(strict_types=1);

namespace Tests\Unit\Payment;

use kissj\Payment\PaymentService;

class PaymentServiceExposed extends PaymentService {
    public function getVariableNumber(?int $prefix): string {
        return parent::getVariableNumber($prefix);
    }
}
