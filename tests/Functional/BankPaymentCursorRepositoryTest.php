<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\BankPayment\BankPayment;
use kissj\BankPayment\BankPaymentRepository;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use Tests\AppTestCase;

class BankPaymentCursorRepositoryTest extends AppTestCase
{
    public function testBankLastMoveIdRoundTrips(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $this->getSmallTestEvent($eventRepository);

        self::assertNull($event->bankLastMoveId);

        $event->bankLastMoveId = 26000000123;
        $eventRepository->persist($event);

        self::assertSame(26000000123, $eventRepository->get($event->id)->bankLastMoveId);
    }

    public function testFindExistingBankIdsReturnsOnlyStoredIdsOfThatEvent(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $bankPaymentRepository = $this->getService($app, BankPaymentRepository::class);
        $event = $this->getSmallTestEvent($eventRepository);
        $otherEvent = $this->getTestSlugEvent($eventRepository);

        $this->createBankPayment($bankPaymentRepository, $event, '101');
        $this->createBankPayment($bankPaymentRepository, $otherEvent, '102');

        self::assertSame(['101'], $bankPaymentRepository->findExistingBankIds($event, ['101', '102', '103']));
        self::assertSame([], $bankPaymentRepository->findExistingBankIds($event, []));
    }

    private function createBankPayment(
        BankPaymentRepository $bankPaymentRepository,
        Event $event,
        string $bankId,
    ): void {
        $bankPayment = new BankPayment();
        $bankPayment->event = $event;
        $bankPayment->bankId = $bankId;
        $bankPayment->status = BankPayment::STATUS_FRESH;
        $bankPaymentRepository->persist($bankPayment);
    }
}
