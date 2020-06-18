<?php

namespace Tests\Functional;


use kissj\PaymentImport\MoneyS3CSV;


class PaymentImportTest extends BaseTestCase {

    /**
     * Test that the index route returns a rendered response containing the text 'SlimFramework' but not a greeting
     */
    public function testImportPaymentData() {
        $importer = new MoneyS3CSV(__DIR__."/money_s3.csv");
        [$payments, $errors] = $importer->getPayments();

        $this->assertEquals(2, count($payments));
        $this->assertEquals(0, count($errors));

        $this->assertEquals("qwefds", $payments[0]->senderName);
        $this->assertEquals(34640609, $payments[0]->variableSymbol);
        $this->assertEquals(250.0, $payments[0]->amount);
        $this->assertEquals(new \DateTimeImmutable("11.10.2017"), $payments[0]->dateReceived);

        $this->assertEquals("sdgdsdfáíé", $payments[1]->senderName);
        $this->assertEquals(34670509, $payments[1]->variableSymbol);
        $this->assertEquals(250.0, $payments[1]->amount);
        $this->assertEquals(new \DateTimeImmutable("09.10.2017"), $payments[1]->dateReceived);
    }
}
