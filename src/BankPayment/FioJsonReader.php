<?php

declare(strict_types=1);

namespace kissj\BankPayment;

use h4kuna\Fio\Exceptions\ServiceUnavailable;
use h4kuna\Fio\Read\Json;
use h4kuna\Fio\Read\TransactionList;
use h4kuna\Fio\Utils\Fio;
use Nette\Utils\Json as NetteJson;
use Nette\Utils\JsonException;
use Psr\Http\Message\ResponseInterface;
use stdClass;

class FioJsonReader extends Json
{
    public function create(ResponseInterface $response): TransactionList
    {
        $content = Fio::getContents($response);
        $response->getBody()->rewind();

        if ($response->getStatusCode() !== 422 && $this->isAccountStatementMissing($content)) {
            throw new ServiceUnavailable(sprintf(
                'Fio response without account statement: %d %s',
                $response->getStatusCode(),
                $response->getReasonPhrase(),
            ));
        }

        return parent::create($response);
    }

    private function isAccountStatementMissing(string $content): bool
    {
        if ($content === '') {
            return true;
        }

        try {
            $json = NetteJson::decode($content);
        } catch (JsonException) {
            return false;
        }

        return ($json instanceof stdClass && isset($json->accountStatement)) === false;
    }
}
