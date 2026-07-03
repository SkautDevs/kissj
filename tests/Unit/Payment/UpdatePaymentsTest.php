<?php

declare(strict_types=1);

namespace Tests\Unit\Payment;

use h4kuna\Fio\Exceptions\ServiceUnavailable;
use kissj\BankPayment\BankPayment;
use kissj\BankPayment\BankPaymentRepository;
use kissj\BankPayment\Banks;
use kissj\BankPayment\BankServiceProvider;
use kissj\BankPayment\IBankPaymentService;
use kissj\Event\Event;
use kissj\Mailer\Mailer;
use kissj\Mailer\MailerSettings;
use kissj\Participant\ParticipantRepository;
use kissj\Payment\Payment;
use kissj\Payment\PaymentMessageSeverity;
use kissj\Payment\PaymentRepository;
use kissj\Payment\PaymentResult;
use kissj\Payment\PaymentResultMessage;
use kissj\Payment\PaymentService;
use kissj\Payment\PaymentSource;
use kissj\Payment\PaymentStatus;
use kissj\Payment\QrCodeService;
use kissj\Telemetry\Metrics;
use kissj\Telemetry\Sentry\Collector;
use kissj\User\LoginTokenRepository;
use kissj\User\UserRepository;
use kissj\User\UserService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Sentry\State\Hub;
use Slim\Views\Twig;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * confirmPayment() is stubbed on a partial mock so only the pairing decisions are exercised here.
 * The readonly collaborators cannot be Mockery-mocked, so they are built as real instances.
 */
class UpdatePaymentsTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private BankPaymentRepository&MockInterface $bankPaymentRepository;
    private PaymentRepository&MockInterface $paymentRepository;
    private ContainerInterface&MockInterface $container;
    private BankServiceProvider $bankServiceProvider;

    protected function setUp(): void
    {
        // deliberately not shouldIgnoreMissing(): an unexpected persist() must fail the test
        $this->bankPaymentRepository = Mockery::mock(BankPaymentRepository::class);
        $this->paymentRepository = Mockery::mock(PaymentRepository::class)->shouldIgnoreMissing();
        $this->container = Mockery::mock(ContainerInterface::class);
        $this->bankServiceProvider = new BankServiceProvider(new Banks(), $this->container);
    }

    private function service(): PaymentService&MockInterface
    {
        $metrics = new Metrics();
        $mailer = new Mailer(
            Mockery::mock(Twig::class),
            Mockery::mock(MailerSettings::class),
            Mockery::mock(QrCodeService::class),
            Mockery::mock(TranslatorInterface::class),
            Mockery::mock(Logger::class),
            $metrics,
        );
        $userService = new UserService(
            Mockery::mock(LoginTokenRepository::class),
            Mockery::mock(ParticipantRepository::class),
            Mockery::mock(UserRepository::class),
            $mailer,
            $metrics,
        );

        return Mockery::mock(PaymentService::class, [
            $this->bankServiceProvider,
            $this->bankPaymentRepository,
            $this->paymentRepository,
            Mockery::mock(ParticipantRepository::class)->shouldIgnoreMissing(),
            $userService,
            $mailer,
            Mockery::mock(LoggerInterface::class)->shouldIgnoreMissing(),
            new Collector(Mockery::mock(Hub::class)->shouldIgnoreMissing()),
            $metrics,
        ])->makePartial();
    }

    private function bankPayment(string $variableSymbol, string $price, string $currency = 'CZK'): BankPayment
    {
        $bankPayment = new BankPayment();
        $bankPayment->variableSymbol = $variableSymbol;
        $bankPayment->price = $price;
        $bankPayment->currency = $currency;
        $bankPayment->status = BankPayment::STATUS_FRESH;

        return $bankPayment;
    }

    private function waitingPayment(string $variableSymbol, string $price, string $currency = 'CZK'): Payment
    {
        $payment = new Payment();
        $payment->id = 1;
        $payment->variableSymbol = $variableSymbol;
        $payment->price = $price;
        $payment->currency = $currency;
        $payment->status = PaymentStatus::Waiting;

        return $payment;
    }

    private function assertHasMessage(PaymentResult $result, PaymentMessageSeverity $severity, string $key, ?string $count = null): void
    {
        $match = array_filter(
            $result->messages,
            fn (PaymentResultMessage $m): bool => $m->severity === $severity && $m->translationKey === $key,
        );
        self::assertCount(1, $match, "expected exactly one {$severity->value} message '{$key}'");

        if ($count !== null) {
            $message = array_values($match)[0];
            self::assertSame($count, $message->translationParams['%count%'] ?? null);
        }
    }

    public function testMatchingVsAndPriceConfirmsAndMarksPaired(): void
    {
        $event = new Event();
        $bankPayment = $this->bankPayment('1234567890', '7700');
        $payment = $this->waitingPayment('1234567890', '7700');

        $this->bankPaymentRepository->shouldReceive('getBankPaymentsOrderedWithStatus')->andReturn([$bankPayment]);
        $this->bankPaymentRepository->shouldReceive('persist')->once()->with($bankPayment);
        $this->paymentRepository->shouldReceive('getWaitingPaymentsKeydByVariableSymbols')
            ->andReturn(['1234567890' => $payment]);

        $service = $this->service();
        $service->shouldReceive('confirmPayment')->once()
            ->with($payment, PaymentSource::AutoBankMatch)
            ->andReturn(new PaymentResult());

        $result = $service->updatePayments($event);

        self::assertSame(BankPayment::STATUS_PAIRED, $bankPayment->status);
        $this->assertHasMessage($result, PaymentMessageSeverity::Success, 'flash.success.adminPairedPayments', '1');
    }

    public function testMatchingVsButDifferentPriceMarksUnknown(): void
    {
        $event = new Event();
        $bankPayment = $this->bankPayment('1234567890', '999');
        $payment = $this->waitingPayment('1234567890', '7700');

        $this->bankPaymentRepository->shouldReceive('getBankPaymentsOrderedWithStatus')->andReturn([$bankPayment]);
        $this->bankPaymentRepository->shouldReceive('persist')->once()->with($bankPayment);
        $this->paymentRepository->shouldReceive('getWaitingPaymentsKeydByVariableSymbols')
            ->andReturn(['1234567890' => $payment]);

        $service = $this->service();
        $service->shouldReceive('confirmPayment')->never();

        $result = $service->updatePayments($event);

        self::assertSame(BankPayment::STATUS_UNKNOWN, $bankPayment->status);
        $this->assertHasMessage($result, PaymentMessageSeverity::Info, 'flash.info.adminPaymentsUnrecognized', '1');
    }

    public function testNoMatchingVariableSymbolMarksUnknown(): void
    {
        $event = new Event();
        $bankPayment = $this->bankPayment('0000000000', '7700');

        $this->bankPaymentRepository->shouldReceive('getBankPaymentsOrderedWithStatus')->andReturn([$bankPayment]);
        $this->bankPaymentRepository->shouldReceive('persist')->once()->with($bankPayment);
        $this->paymentRepository->shouldReceive('getWaitingPaymentsKeydByVariableSymbols')->andReturn([]);

        $service = $this->service();
        $service->shouldReceive('confirmPayment')->never();

        $result = $service->updatePayments($event);

        self::assertSame(BankPayment::STATUS_UNKNOWN, $bankPayment->status);
        $this->assertHasMessage($result, PaymentMessageSeverity::Info, 'flash.info.adminPaymentsUnrecognized', '1');
    }

    public function testOnlyLimitPaymentsAreProcessedPerRun(): void
    {
        $event = new Event();
        $bankPayments = [];
        for ($i = 0; $i < 12; $i++) {
            $bankPayments[] = $this->bankPayment(str_pad((string)$i, 10, '0', STR_PAD_LEFT), '7700');
        }

        $this->bankPaymentRepository->shouldReceive('getBankPaymentsOrderedWithStatus')->andReturn($bankPayments);
        for ($i = 0; $i < 10; $i++) {
            $this->bankPaymentRepository->shouldReceive('persist')->once()->with($bankPayments[$i]);
        }
        $this->paymentRepository->shouldReceive('getWaitingPaymentsKeydByVariableSymbols')->andReturn([]);

        $service = $this->service();
        $service->shouldReceive('confirmPayment')->never();

        $service->updatePayments($event, 10);

        for ($i = 0; $i < 10; $i++) {
            self::assertSame(BankPayment::STATUS_UNKNOWN, $bankPayments[$i]->status, "payment {$i} should be processed");
        }
        self::assertSame(BankPayment::STATUS_FRESH, $bankPayments[10]->status, 'payment beyond the limit stays fresh');
        self::assertSame(BankPayment::STATUS_FRESH, $bankPayments[11]->status, 'payment beyond the limit stays fresh');
    }

    public function testNoFreshPaymentsFetchesNewOnesFromBank(): void
    {
        $event = new Event();
        $event->bankSlug = 'fio';

        $bankService = Mockery::mock(IBankPaymentService::class);
        $bankService->shouldReceive('getAndSafeFreshPaymentsFromBank')->with($event)->andReturn(3);
        $this->container->shouldReceive('get')->andReturn($bankService);
        $this->bankPaymentRepository->shouldReceive('getBankPaymentsOrderedWithStatus')->andReturn([]);

        $result = $this->service()->updatePayments($event);

        $this->assertHasMessage($result, PaymentMessageSeverity::Info, 'flash.info.newPayments', '3');
    }

    public function testNoFreshPaymentsAndNothingNewFromBank(): void
    {
        $event = new Event();
        $event->bankSlug = 'fio';

        $bankService = Mockery::mock(IBankPaymentService::class);
        $bankService->shouldReceive('getAndSafeFreshPaymentsFromBank')->andReturn(0);
        $this->container->shouldReceive('get')->andReturn($bankService);
        $this->bankPaymentRepository->shouldReceive('getBankPaymentsOrderedWithStatus')->andReturn([]);

        $result = $this->service()->updatePayments($event);

        $this->assertHasMessage($result, PaymentMessageSeverity::Info, 'flash.info.noNewPayments');
    }

    public function testBankUnavailableReturnsErrorMessage(): void
    {
        $event = new Event();
        $event->id = 1;
        $event->bankSlug = 'fio';

        $bankService = Mockery::mock(IBankPaymentService::class);
        $bankService->shouldReceive('getAndSafeFreshPaymentsFromBank')->andThrow(new ServiceUnavailable('bank down'));
        $this->container->shouldReceive('get')->andReturn($bankService);
        $this->bankPaymentRepository->shouldReceive('getBankPaymentsOrderedWithStatus')->andReturn([]);

        $result = $this->service()->updatePayments($event);

        $this->assertHasMessage($result, PaymentMessageSeverity::Error, 'flash.error.fioConnectionFailed');
    }

    // pins the current buggy behavior (PAY-3); the CZK/EUR mix is paired on purpose, do not "fix" this test
    public function testCurrencyIsIgnoredWhenMatching_currentBehavior(): void
    {
        $event = new Event();
        $bankPayment = $this->bankPayment('1234567890', '350', 'CZK');
        $payment = $this->waitingPayment('1234567890', '350', 'EUR');

        $this->bankPaymentRepository->shouldReceive('getBankPaymentsOrderedWithStatus')->andReturn([$bankPayment]);
        $this->bankPaymentRepository->shouldReceive('persist')->once()->with($bankPayment);
        $this->paymentRepository->shouldReceive('getWaitingPaymentsKeydByVariableSymbols')
            ->andReturn(['1234567890' => $payment]);

        $service = $this->service();
        $service->shouldReceive('confirmPayment')->once()->andReturn(new PaymentResult());

        $service->updatePayments($event);

        self::assertSame(BankPayment::STATUS_PAIRED, $bankPayment->status);
    }
}
