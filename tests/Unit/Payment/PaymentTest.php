<?php declare(strict_types=1);

namespace Tests\Unit\Payment;

use kissj\Application\DateTimeUtils;
use kissj\Payment\Payment;
use kissj\Payment\PaymentStatus;
use PHPUnit\Framework\TestCase;

class PaymentTest extends TestCase
{
    /**
     * @param Payment $payment
     * @param string $expectedQrCode
     * @return void
     * 
     * @dataProvider provideGetQrCode
     */
    public function testGetQrCode(
        Payment $payment,
        string $expectedQrCode,
    ): void {
        $this->assertSame($expectedQrCode, $payment->getQrPaymentString());
    }

    /**
     * @return array<string, array<Payment|string>>
     */
    public function provideGetQrCode(): array
    {
        $examplePayment0 = new Payment();
        $examplePayment0->variableSymbol = '1212';
        $examplePayment0->price = '999';
        $examplePayment0->currency = 'CZK';
        $examplePayment0->status = PaymentStatus::Waiting;
        $examplePayment0->purpose = 'fee';
        $examplePayment0->accountNumber = '';
        $examplePayment0->iban = 'CZ5608000000000002171532';
        $examplePayment0->due = DateTimeUtils::getDateTime('2015-05-18');
        $examplePayment0->note = 'Zpráva ěščŘŽÝÁíé';

        $examplePayment1 = new Payment();
        $examplePayment1->variableSymbol = '1234567890';
        $examplePayment1->price = '450';
        $examplePayment1->currency = 'CZK';
        $examplePayment1->status = PaymentStatus::Waiting;
        $examplePayment1->purpose = 'fee';
        $examplePayment1->accountNumber = '';
        $examplePayment1->iban = 'CZ2806000000000168540115';
        $examplePayment1->due = DateTimeUtils::getDateTime('2022-05-21');
        $examplePayment1->note = 'PLATBA ZA ZBOZI';

        return [
            'examplePayment with diacritic' => [
                $examplePayment0,
                'SPD*1.0*ACC:CZ5608000000000002171532*AM:999*CC:CZK*DT:20150518*MSG:Zprava escRZYAie*X-VS:1212'
            ],
            'examplePayment' => [
                $examplePayment1,
                'SPD*1.0*ACC:CZ2806000000000168540115*AM:450*CC:CZK*DT:20220521*MSG:PLATBA ZA ZBOZI*X-VS:1234567890'
            ],
        ];
    }
}
