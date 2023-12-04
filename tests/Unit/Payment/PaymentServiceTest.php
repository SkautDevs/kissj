<?php declare(strict_types=1);

namespace Tests\Unit\Payment;

use kissj\BankPayment\BankPaymentRepository;
use kissj\BankPayment\FioBankPaymentService;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Logging\Sentry\SentryCollector;
use kissj\Mailer\PhpMailerWrapper;
use kissj\Participant\ParticipantRepository;
use kissj\Payment\PaymentRepository;
use kissj\User\UserService;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class PaymentServiceTest extends TestCase
{
    public function testGetVariableNumber(): void
    {
        $paymentService = new PaymentServiceExposed(
            \Mockery::mock(FioBankPaymentService::class),
            \Mockery::mock(BankPaymentRepository::class),
            \Mockery::mock(PaymentRepository::class),
            \Mockery::mock(ParticipantRepository::class),
            \Mockery::mock(UserService::class),
            \Mockery::mock(FlashMessagesBySession::class),
            \Mockery::mock(PhpMailerWrapper::class),
            \Mockery::mock(TranslatorInterface::class),
            \Mockery::mock(Logger::class),
            \Mockery::mock(SentryCollector::class),
        );

        for ($i = 0; $i < 100; $i++) {
            $prefix = random_int(1, 9999);
            $variableNumber = $paymentService->getVariableNumber($prefix);
            $this->assertEquals(10, strlen($variableNumber));
            $this->assertEquals($prefix, substr($variableNumber, 0, strlen((string)$prefix)));
        }

        for ($i = 0; $i < 10; $i++) {
            $variableNumber = $paymentService->getVariableNumber(null);
            $this->assertEquals(10, strlen($variableNumber));
        }
    }
}
