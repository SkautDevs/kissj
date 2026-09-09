<?php

declare(strict_types=1);

namespace Tests\Unit\Payment;

use kissj\Participant\Participant;
use kissj\Payment\FinanceTier;
use kissj\Payment\FinanceTierUnknown;
use kissj\Payment\Payment;
use kissj\Payment\PaymentStatus;
use kissj\Payment\ScarfFinanceTier;
use Tests\AppTestCase;

class FinanceTierTest extends AppTestCase
{
    public function testFinanceTierMatchesOnPriceEqualityAgainstStringPrice(): void
    {
        $tier = new FinanceTier(600);
        $payment = new Payment(['price' => '600']);

        self::assertTrue($tier->matches($payment));
    }

    public function testFinanceTierDoesNotMatchDifferentPrice(): void
    {
        $tier = new FinanceTier(600);
        $payment = new Payment(['price' => '450']);

        self::assertFalse($tier->matches($payment));
    }

    public function testFinanceTierUnknownNeverMatchesEvenAgainstZeroPrice(): void
    {
        $tier = new FinanceTierUnknown();
        $payment = new Payment(['price' => '0']);

        self::assertFalse($tier->matches($payment));
        self::assertTrue($tier->isUnknown());
        self::assertFalse((new FinanceTier(0))->isUnknown());
    }

    public function testScarfFinanceTierMatchesOnlyWhenPriceAndScarfBothAgree(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);
        $payment = $this->createFinancesPayment($app, $event, PaymentStatus::Paid, '600', scarf: Participant::SCARF_YES);

        $tier = new ScarfFinanceTier(600, true);

        self::assertTrue($tier->matches($payment));
    }

    public function testScarfFinanceTierDoesNotMatchWhenScarfDisagrees(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);
        $payment = $this->createFinancesPayment($app, $event, PaymentStatus::Paid, '600', scarf: Participant::SCARF_NO);

        $tier = new ScarfFinanceTier(600, true);

        self::assertFalse($tier->matches($payment));
    }

    public function testScarfFinanceTierDoesNotMatchWhenPriceDisagrees(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);
        $payment = $this->createFinancesPayment($app, $event, PaymentStatus::Paid, '450', scarf: Participant::SCARF_YES);

        $tier = new ScarfFinanceTier(600, true);

        self::assertFalse($tier->matches($payment));
    }

    public function testScarfFinanceTierTreatsNullParticipantScarfAsNoScarf(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);
        $payment = $this->createFinancesPayment($app, $event, PaymentStatus::Paid, '600');
        $payment->participant->scarf = null;

        $tier = new ScarfFinanceTier(600, false);

        self::assertTrue($tier->matches($payment));
    }
}
