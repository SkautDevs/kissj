<?php

namespace kissj\BankPayment;

use h4kuna\Fio\FioRead;
use h4kuna\Fio\Utils\FioFactory;
use kissj\Event\Event;

class FioBankReaderFactory
{
    private ?FioRead $event = null;

    public function getFioRead(Event $event): FioRead
    {
        if ($this->event === null) {
            $this->event = $this->createFioRead($event);
        }

        return $this->event;
    }

    private function createFioRead(Event $event): FioRead
    {
        // using h4kuna/fio - https://github.com/h4kuna/fio
        $fioAccountName = 'fio-account';
        $fioFactory = new FioFactory([
            $fioAccountName => [
                'account' => $event->accountNumber,
                'token' => $event->bankApiKey ?? '',
            ],
        ]);

        return $fioFactory->createFioRead($fioAccountName);
    }
}
