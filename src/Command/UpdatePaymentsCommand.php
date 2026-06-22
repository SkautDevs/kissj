<?php

declare(strict_types=1);

namespace kissj\Command;

use kissj\Event\EventRepository;
use kissj\Event\EventScope;
use kissj\Payment\PaymentService;
use kissj\Translation\CurrentTranslator;
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
        private readonly EventScope $eventScope,
        private readonly CurrentTranslator $translator,
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $events = $this->eventRepository->findActiveNontestAutopaymentsOnEvents();
        $output->writeln('Updating payments for ' . count($events) . ' events...');

        foreach ($events as $event) {
            $this->eventScope->apply(
                $event,
                // ugly hack, but it is complicated to get RouterCollector in command
                sprintf('https://kissj.net/%s', $event->slug), // production address
            );

            // No Accept-Language to negotiate in CLI - use the event's primary language.
            $languages = array_keys($event->getEventType()->getLanguages());
            if ($languages !== []) {
                $this->translator->setLocale($languages[0]);
            }

            $result = $this->paymentService->updatePayments($event);
            foreach ($result->messages as $message) {
                $output->writeln(sprintf('[%s] %s', $message->severity->value, $message->translationKey));
            }
        }

        return Command::SUCCESS;
    }
}
