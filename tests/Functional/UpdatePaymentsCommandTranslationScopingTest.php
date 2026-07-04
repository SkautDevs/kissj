<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\BankPayment\BankPayment;
use kissj\BankPayment\BankPaymentRepository;
use kissj\Command\UpdatePaymentsCommand;
use kissj\Event\EventRepository;
use kissj\Translation\CurrentTranslator;
use LeanMapper\Connection;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\AppTestCase;

class UpdatePaymentsCommandTranslationScopingTest extends AppTestCase
{
    public function testCommandAppliesEventTypeTranslationOverrides(): void
    {
        $app = $this->getTestApp();

        $connection = $this->getService($app, Connection::class);
        $connection->query(
            'UPDATE event SET event_type = %s, automatic_payment_pairing = %b,
                 start_registration = %s, end_day = %s WHERE slug = %s',
            'wsj',
            true,
            DateTimeUtils::getDateTime('2000-01-01')->format(DATE_ATOM),
            DateTimeUtils::getDateTime('2099-01-01')->format(DATE_ATOM),
            'test-event-slug',
        );

        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->findBySlug('test-event-slug');
        self::assertNotNull($event);

        // Seed a fresh, non-matching bank payment so the command pairs locally
        // instead of reaching out to the bank API during the test.
        $bankPayment = new BankPayment();
        $bankPayment->event = $event;
        $bankPayment->status = BankPayment::STATUS_FRESH;
        $bankPayment->price = '1';
        $bankPayment->variableSymbol = '000000';
        $bankPaymentRepository = $this->getService($app, BankPaymentRepository::class);
        $bankPaymentRepository->persist($bankPayment);

        $key = 'chooseRole.registerAsIst';
        $baseValue = 'Registrovat se do servis týmu!';
        $wsjValue = 'Registrovat se do IST!';

        $translator = $this->getService($app, CurrentTranslator::class);
        // Outside an HTTP request no delegate is set, so base translations apply.
        self::assertSame($baseValue, $translator->trans($key));

        $command = $this->getService($app, UpdatePaymentsCommand::class);
        $tester = new CommandTester($command);
        $exitCode = $tester->execute([]);

        self::assertSame(0, $exitCode);
        // The command must scope translations to the processed event's type,
        // otherwise emails it sends (e.g. payment-successful) lose event overrides.
        self::assertSame($wsjValue, $translator->trans($key));
    }
}
