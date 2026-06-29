<?php declare(strict_types=1);

namespace Tests\Functional;

use DateTimeImmutable;
use kissj\BankPayment\BankPayment;
use kissj\BankPayment\BankPaymentRepository;
use kissj\Command\UpdatePaymentsCommand;
use kissj\Event\EventRepository;
use kissj\Translation\CurrentTranslator;
use LeanMapper\Connection;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\AppTestCase;

class UpdatePaymentsCommandTranslationScopingTest extends AppTestCase
{
    public function testCommandAppliesEventTypeTranslationOverrides(): void
    {
        $app = $this->getTestApp();
        /** @var ContainerInterface $container */
        $container = $app->getContainer();

        /** @var Connection $connection */
        $connection = $container->get(Connection::class);
        $connection->query(
            'UPDATE event SET event_type = %s, automatic_payment_pairing = %b,
                 start_registration = %s, end_day = %s WHERE slug = %s',
            'wsj',
            true,
            (new DateTimeImmutable('2000-01-01'))->format(DATE_ATOM),
            (new DateTimeImmutable('2099-01-01'))->format(DATE_ATOM),
            'test-event-slug',
        );

        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        $event = $eventRepository->findBySlug('test-event-slug');
        self::assertNotNull($event);

        // Seed a fresh, non-matching bank payment so the command pairs locally
        // instead of reaching out to the bank API during the test.
        $bankPayment = new BankPayment();
        $bankPayment->event = $event;
        $bankPayment->status = BankPayment::STATUS_FRESH;
        $bankPayment->price = '1';
        $bankPayment->variableSymbol = '000000';
        /** @var BankPaymentRepository $bankPaymentRepository */
        $bankPaymentRepository = $container->get(BankPaymentRepository::class);
        $bankPaymentRepository->persist($bankPayment);

        $key = 'chooseRole.registerAsIst';
        $baseValue = 'Registrovat se do servis týmu!';
        $wsjValue = 'Registrovat se do IST!';

        /** @var CurrentTranslator $translator */
        $translator = $container->get(CurrentTranslator::class);
        // Outside an HTTP request no delegate is set, so base translations apply.
        self::assertSame($baseValue, $translator->trans($key));

        /** @var UpdatePaymentsCommand $command */
        $command = $container->get(UpdatePaymentsCommand::class);
        $tester = new CommandTester($command);
        $exitCode = $tester->execute([]);

        self::assertSame(0, $exitCode);
        // The command must scope translations to the processed event's type,
        // otherwise emails it sends (e.g. payment-successful) lose event overrides.
        self::assertSame($wsjValue, $translator->trans($key));
    }
}
