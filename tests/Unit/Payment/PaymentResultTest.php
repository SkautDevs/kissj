<?php

declare(strict_types=1);

namespace Tests\Unit\Payment;

use kissj\Payment\Payment;
use kissj\Payment\PaymentMessageSeverity;
use kissj\Payment\PaymentResult;
use kissj\Payment\PaymentResultMessage;
use PHPUnit\Framework\TestCase;

class PaymentResultTest extends TestCase
{
    public function testSuccessCreatesResultWithSuccessMessage(): void
    {
        $payment = $this->createMock(Payment::class);
        $result = PaymentResult::success($payment, 'flash.success.test');

        self::assertSame($payment, $result->payment);
        self::assertCount(1, $result->messages);
        self::assertSame(PaymentMessageSeverity::Success, $result->messages[0]->severity);
        self::assertSame('flash.success.test', $result->messages[0]->translationKey);
    }

    public function testWarningCreatesResultWithWarningMessage(): void
    {
        $payment = $this->createMock(Payment::class);
        $result = PaymentResult::warning($payment, 'flash.warning.test');

        self::assertSame(PaymentMessageSeverity::Warning, $result->messages[0]->severity);
    }

    public function testWithMessagesCreatesResultWithMultipleMessages(): void
    {
        $messages = [
            new PaymentResultMessage(PaymentMessageSeverity::Success, 'flash.success.paired'),
            new PaymentResultMessage(PaymentMessageSeverity::Info, 'flash.info.unrecognized'),
        ];
        $result = PaymentResult::withMessages($messages);

        self::assertNull($result->payment);
        self::assertCount(2, $result->messages);
    }
}
