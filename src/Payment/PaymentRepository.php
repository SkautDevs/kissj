<?php

namespace kissj\Payment;

use kissj\Orm\Repository;

class PaymentRepository extends Repository {
    public function isVariableNumberExisting(string $variableNumber): bool {
        return $this->isExisting(['variable_symbol' => $variableNumber]);
    }
}
