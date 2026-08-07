<?php

declare(strict_types=1);

namespace Tests\Unit\Event\EventType;

use kissj\Event\EventType\Cej\EventTypeCej;
use kissj\Participant\Ist\Ist;
use kissj\Payment\Payment;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CejAccountNumberTest extends TestCase
{
    #[DataProvider('provideContingentAccountNumbers')]
    public function testTransformPaymentPriceSetsAccountNumberPerContingent(
        string $contingent,
        string $expectedAccountNumber,
    ): void {
        $ist = new Ist();
        $ist->contingent = $contingent;

        $payment = new Payment();
        $payment->variableSymbol = '1234567890';
        $payment->note = 'note';

        $payment = (new EventTypeCej())->transformPaymentPrice($payment, $ist);

        self::assertSame($expectedAccountNumber, $payment->accountNumber);
        self::assertNotSame('', $payment->iban);
        self::assertNotSame('', $payment->swift);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function provideContingentAccountNumbers(): array
    {
        return [
            'poland' => [EventTypeCej::CONTINGENT_POLAND, ''],
            'romania' => [EventTypeCej::CONTINGENT_ROMANIA, ''],
            'czechia' => [EventTypeCej::CONTINGENT_CZECHIA, '2302084720/2010'],
            'slovakia' => [EventTypeCej::CONTINGENT_SLOVAKIA, '2660080180/1100'],
            'hungary' => [EventTypeCej::CONTINGENT_HUNGARY, '10918001-00000071-76940552'],
        ];
    }
}
