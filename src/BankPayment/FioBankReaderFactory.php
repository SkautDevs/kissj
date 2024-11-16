<?php

namespace kissj\BankPayment;

use h4kuna\Fio\FioRead;
use h4kuna\Fio\FioFactory;
use kissj\Event\Event;

/**
 * reader needs to be created in run-time because of dynamic Event
 */
class FioBankReaderFactory
{
    private ?FioRead $fioRead = null;

    public function getFioRead(Event $event): FioRead
    {
        if ($this->fioRead === null) {
            $this->fioRead = $this->createFioRead($event);
        }

        return $this->fioRead;
    }

    private function createFioRead(Event $event): FioRead
    {
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
