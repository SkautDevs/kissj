<?php

declare(strict_types=1);

namespace kissj\Command;

use kissj\Event\EventRepository;
use kissj\Logging\Sentry\SentryCollector;
use kissj\Mailer\MailerSettings;
use kissj\Payment\PaymentService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:update-payments',
    description: 'Update all payments for active non-test autopayments on events',
)]
class UpdatePaymentsCommand extends Command
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly EventRepository $eventRepository,
        private readonly MailerSettings $mailerSettings,
        private readonly SentryCollector $sentryCollector,
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $events = $this->eventRepository->findActiveNontestAutopaymentsOnEvents();
            $output->writeln('Updating payments for ' . count($events) . ' events...');

            foreach ($events as $event) {
                $this->mailerSettings->setEvent($event);
                $this->mailerSettings->setFullUrlLink(
                    // ugly hack, but it is complicated to get RouterCollector in command
                    sprintf("https://kissj.net/%s", $event->slug), // production address
                );
                $this->paymentService->updatePayments($event);
            }

            return Command::SUCCESS;
        } catch (\Throwable $t) {
            $this->sentryCollector->collect($t);

            throw $t;
        }
    }
}
