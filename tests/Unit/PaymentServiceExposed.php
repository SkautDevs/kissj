<?php

namespace Tests\Unit;

use kissj\Payment\PaymentService;

class PaymentServiceExposed extends PaymentService {
    public function getVariableNumber(?int $prefix): string {
        return parent::getVariableNumber($prefix);
    }
}
