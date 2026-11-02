<?php

declare(strict_types=1);

namespace Tests\Unit\BankPayment;

use GuzzleHttp\Psr7\Response;
use h4kuna\Fio\Exceptions\LowAuthorization;
use h4kuna\Fio\Exceptions\ServiceUnavailable;
use h4kuna\Fio\Read\Transaction;
use kissj\BankPayment\FioJsonReader;
use PHPUnit\Framework\TestCase;

class FioJsonReaderTest extends TestCase
{
    public function testEmptyBodyThrowsServiceUnavailable(): void
    {
        $this->expectException(ServiceUnavailable::class);
        $this->expectExceptionMessage('Fio response without account statement: 200 OK');

        (new FioJsonReader())->create(new Response(200, [], ''));
    }

    public function testBodyWithoutAccountStatementThrowsServiceUnavailable(): void
    {
        $this->expectException(ServiceUnavailable::class);
        $this->expectExceptionMessage('Fio response without account statement: 200 OK');

        (new FioJsonReader())->create(new Response(200, [], '{}'));
    }

    public function testInvalidJsonThrowsServiceUnavailable(): void
    {
        $this->expectException(ServiceUnavailable::class);

        (new FioJsonReader())->create(new Response(200, [], 'not json'));
    }

    public function testInsufficientAuthorizationKeepsLibraryException(): void
    {
        $this->expectException(LowAuthorization::class);

        (new FioJsonReader())->create(new Response(422, [], ''));
    }

    public function testStatementBodyIsParsed(): void
    {
        $body = '{"accountStatement":{"info":{"idLastDownload":100,"idTo":101},"transactionList":{"transaction":['
            . '{"column0":{"value":"2026-10-01+0200"},"column1":{"value":500.0},"column22":{"value":101}}]}}}';

        $transactionList = (new FioJsonReader())->create(new Response(200, [], $body));

        self::assertSame(101, $transactionList->getInfo()->idTo);
        $transactions = iterator_to_array($transactionList, false);
        self::assertCount(1, $transactions);
        $transaction = $transactions[0];
        self::assertInstanceOf(Transaction::class, $transaction);
        self::assertSame(101, $transaction->moveId);
    }
}
