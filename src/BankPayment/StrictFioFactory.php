<?php

declare(strict_types=1);

namespace kissj\BankPayment;

use h4kuna\Fio\FioFactory as LibraryFioFactory;
use h4kuna\Fio\Read\Json;

class StrictFioFactory extends LibraryFioFactory
{
    protected function createReader(): Json
    {
        return new FioJsonReader();
    }
}
