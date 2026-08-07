<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\EventRepository;
use kissj\Payment\Payment;
use Slim\Views\Twig;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tests\AppTestCase;

class PaymentInfoEmailTemplateTest extends AppTestCase
{
    public function testAccountNumberLineRendersOnlyWhenAccountNumberIsPresent(): void
    {
        $app = $this->getTestApp();
        $twig = $this->getService($app, Twig::class);
        $translator = $this->getService($app, TranslatorInterface::class);
        $event = $this->getSmallTestEvent($this->getService($app, EventRepository::class));
        $accountNumberLabel = $translator->trans('email.payment-info.accountNumber');

        $htmlWithoutAccountNumber = $twig->fetch('emails/payment-info.twig', [
            'event' => $event,
            'payment' => $this->createPayment(''),
            'showIban' => true,
            'showPaymentQrCode' => false,
            'genderSuffix' => '',
        ]);
        self::assertStringNotContainsString($accountNumberLabel, $htmlWithoutAccountNumber);

        $htmlWithAccountNumber = $twig->fetch('emails/payment-info.twig', [
            'event' => $event,
            'payment' => $this->createPayment('2302084720/2010'),
            'showIban' => true,
            'showPaymentQrCode' => false,
            'genderSuffix' => '',
        ]);
        self::assertStringContainsString($accountNumberLabel, $htmlWithAccountNumber);
        self::assertStringContainsString('2302084720/2010', $htmlWithAccountNumber);
    }

    private function createPayment(string $accountNumber): Payment
    {
        $payment = new Payment();
        $payment->variableSymbol = '1234567890';
        $payment->constantSymbol = '';
        $payment->price = '450';
        $payment->currency = 'CZK';
        $payment->accountNumber = $accountNumber;
        $payment->iban = 'CZ31 2010 0000 0023 0208 4720';
        $payment->swift = 'FIOBCZPP';
        $payment->due = DateTimeUtils::getDateTime('2026-09-30');
        $payment->note = 'note';

        return $payment;
    }
}
