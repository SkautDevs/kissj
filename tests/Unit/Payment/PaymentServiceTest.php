<?php declare(strict_types=1);

namespace Tests\Unit\Payment;

use kissj\BankPayment\BankPaymentRepository;
use kissj\BankPayment\Banks;
use kissj\BankPayment\BankServiceProvider;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Logging\Sentry\SentryCollector;
use kissj\Mailer\Mailer;
use kissj\Mailer\MailerSettings;
use kissj\Participant\ParticipantRepository;
use kissj\Payment\PaymentRepository;
use kissj\Payment\QrCodeService;
use kissj\User\LoginTokenRepository;
use kissj\User\UserRepository;
use kissj\User\UserService;
use Mockery;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Sentry\State\Hub;
use Slim\Views\Twig;
use Symfony\Contracts\Translation\TranslatorInterface;

class PaymentServiceTest extends TestCase
{
    public function testGenerateVariableNumber(): void
    {
        $mailerMock = new Mailer(
            Mockery::mock(Twig::class),
            Mockery::mock(MailerSettings::class),
            Mockery::mock(QrCodeService::class),
            Mockery::mock(TranslatorInterface::class),
            Mockery::mock(Logger::class),
        );
        $paymentService = new PaymentServiceExposed(
            new BankServiceProvider(
                new Banks(),
                Mockery::mock(ContainerInterface::class),
            ),
            Mockery::mock(BankPaymentRepository::class),
            Mockery::mock(PaymentRepository::class),
            Mockery::mock(ParticipantRepository::class),
            new UserService(
                Mockery::mock(LoginTokenRepository::class),
                Mockery::mock(ParticipantRepository::class),
                Mockery::mock(UserRepository::class),
                Mockery::mock(PaymentRepository::class),
                $mailerMock,
            ),
            Mockery::mock(FlashMessagesBySession::class),
            $mailerMock,
            Mockery::mock(TranslatorInterface::class),
            Mockery::mock(Logger::class),
            new SentryCollector(
                Mockery::mock(Hub::class),
            ),
        );

        for ($i = 0; $i < 100; $i++) {
            $prefix = random_int(1, 9999);
            $variableNumber = $paymentService->generateVariableNumber($prefix);
            $this->assertEquals(10, strlen($variableNumber));
            $this->assertEquals($prefix, substr($variableNumber, 0, strlen((string)$prefix)));
        }

        for ($i = 0; $i < 10; $i++) {
            $variableNumber = $paymentService->generateVariableNumber(null);
            $this->assertEquals(10, strlen($variableNumber));
        }
    }
}
