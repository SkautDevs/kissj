<?php

namespace kissj\BankPayment;

use h4kuna\Fio\FioRead;
use h4kuna\Fio\FioFactory;
use kissj\Event\Event;

/**
 * reader needs to be created in run-time because of need dynamic multiple events during single request
 */
class FioBankReaderFactory
{
    public function getFioRead(Event $event): FioRead
    {
        $fioAccountName = 'fio-account-' . $event->slug;
        $fioFactory = new FioFactory([
            $fioAccountName => [
                'account' => $event->accountNumber,
                'token' => $event->bankApiKey ?? '',
            ],
        ]);

        return $fioFactory->createFioRead($fioAccountName);
    }
}
