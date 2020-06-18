<?php

namespace Tests\Functional;


use kissj;
use kissj\Payment\PaymentRepository;
use kissj\PaymentImport\PaymentMatcherService;


class PaymentMatcherTest extends BaseTestCase {

    public function testPaymentMatcher() {
        $app = $this->app();
        /** @var PaymentMatcherService $matcher */
        $matcher = $app->getContainer()->get('paymentMatcherService');

        /** @var PaymentRepository $paymentRepository */
        $paymentRepository = $app->getContainer()->get('paymentRepository');

        $payment = new kissj\Payment\Payment();
        $payment->variableSymbol = "34";
        $payment->currency = "CZK";
        $payment->event = "cej2018";
        $payment->id = 0;
        $payment->price = 300;
        $payment->purpose = "idontknow";
        $payment->status = "waiting";
        $paymentRepository->persist($payment);

        $this->assertTrue(false);
    }
}
