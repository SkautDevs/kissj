<?php

declare(strict_types=1);

namespace kissj\Command;

use kissj\Event\EventRepository;
use kissj\Payment\PaymentService;
use Symfony\Component\Console\Command\Command;

// TODO
class UpdatePaymentsCommand extends Command
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly EventRepository $eventRepository,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        foreach ($this->eventRepository->findActiveNontestAutopaymentsOnEvents() as $event) {
            $this->paymentService->updatePayments($event);
        }
    }
}
