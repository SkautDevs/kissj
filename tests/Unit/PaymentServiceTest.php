<?php

namespace Tests\Unit;

use kissj\BankPayment\BankPaymentRepository;
use kissj\BankPayment\FioBankPaymentService;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Mailer\PhpMailerWrapper;
use kissj\Payment\PaymentRepository;
use kissj\User\UserService;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class PaymentServiceTest extends TestCase {
    public function testGetVariableNumber() {
        $paymentService = new PaymentServiceExposed(
            \Mockery::mock(FioBankPaymentService::class),
            \Mockery::mock(BankPaymentRepository::class),
            \Mockery::mock(PaymentRepository::class),
            \Mockery::mock(UserService::class),
            \Mockery::mock(FlashMessagesBySession::class),
            \Mockery::mock(PhpMailerWrapper::class),
            \Mockery::mock(TranslatorInterface::class),
            \Mockery::mock(Logger::class)
        );

        for ($i = 0; $i < 100; $i++) {
            $variableNumber = $paymentService->getVariableNumber(random_int(1, 9999));
            $this->assertEquals(10, strlen($variableNumber));
        }

        for ($i = 0; $i < 10; $i++) {
            $variableNumber = $paymentService->getVariableNumber(null);
            $this->assertEquals(10, strlen($variableNumber));
        }
    }
}
