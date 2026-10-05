<?php

declare(strict_types=1);

namespace Tests\Functional;

use ArrayObject;
use GuzzleHttp\Psr7\Response;
use h4kuna\Fio\Exceptions\ServiceUnavailable;
use h4kuna\Fio\FioRead;
use h4kuna\Fio\Read\TransactionList;
use kissj\BankPayment\BankPayment;
use kissj\BankPayment\BankPaymentRepository;
use kissj\BankPayment\FioBankPaymentService;
use kissj\BankPayment\FioBankReaderFactory;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use stdClass;
use Tests\AppTestCase;

class FioBankPaymentServiceTest extends AppTestCase
{
    use MockeryPHPUnitIntegration;

    private FioRead&MockInterface $fioRead;
    private LoggerInterface&MockInterface $logger;
    private EventRepository $eventRepository;
    private BankPaymentRepository $bankPaymentRepository;
    private FioBankPaymentService $service;
    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        $app = $this->getTestApp();
        $this->eventRepository = $this->getService($app, EventRepository::class);
        $this->bankPaymentRepository = $this->getService($app, BankPaymentRepository::class);

        $this->event = $this->getSmallTestEvent($this->eventRepository);

        $this->fioRead = Mockery::mock(FioRead::class);
        $readerFactory = Mockery::mock(FioBankReaderFactory::class);
        $readerFactory->shouldReceive('getFioRead')->andReturn($this->fioRead);
        $this->logger = Mockery::mock(LoggerInterface::class);

        $this->service = new FioBankPaymentService(
            $this->bankPaymentRepository,
            $this->eventRepository,
            $readerFactory,
            $this->logger,
        );
    }

    public function testMatchedCursorMakesSingleRequestAndAdvancesCursor(): void
    {
        $this->setCursor(100);
        $this->fioRead->shouldReceive('lastDownload')->once()->andReturn(
            $this->transactionList(100, 102, [[101, 500.0], [102, -200.0]]),
        );
        $this->fioRead->shouldNotReceive('setLastId');
        $this->fioRead->shouldNotReceive('setLastDate');

        self::assertSame(1, $this->service->getAndSafeFreshPaymentsFromBank($this->event));

        self::assertSame(['101'], $this->storedBankIds());
        self::assertSame(102, $this->reloadedCursor());
    }

    public function testKnownBankIdIsSkipped(): void
    {
        $this->setCursor(100);
        $this->storeBankPayment('101');
        $this->fioRead->shouldReceive('lastDownload')->once()->andReturn(
            $this->transactionList(100, 103, [[101, 500.0], [103, 700.0]]),
        );

        self::assertSame(1, $this->service->getAndSafeFreshPaymentsFromBank($this->event));

        self::assertSame(['101', '103'], $this->storedBankIds());
        self::assertSame(103, $this->reloadedCursor());
    }

    public function testEmptyDownloadKeepsCursorAtBookmark(): void
    {
        $this->setCursor(100);
        $this->fioRead->shouldReceive('lastDownload')->once()->andReturn($this->transactionList(100, null, []));
        $this->fioRead->shouldNotReceive('setLastId');

        self::assertSame(0, $this->service->getAndSafeFreshPaymentsFromBank($this->event));

        self::assertSame(100, $this->reloadedCursor());
    }

    public function testBootstrapFromLastSavedPayment(): void
    {
        $this->storeBankPayment('90');
        $this->fioRead->shouldReceive('setLastId')->with(90)->once()->ordered()->andReturn($this->response(200));
        $this->fioRead->shouldReceive('lastDownload')->once()->ordered()->andReturn(
            $this->transactionList(90, 101, [[101, 500.0]]),
        );

        self::assertSame(1, $this->service->getAndSafeFreshPaymentsFromBank($this->event));

        self::assertSame(101, $this->reloadedCursor());
    }

    public function testBootstrapWithoutPaymentsStoresBookmarkFromEmptyDownload(): void
    {
        $this->fioRead->shouldReceive('setLastDate')->with('now - 89 days')->once()->ordered()->andReturn($this->response(200));
        $this->fioRead->shouldReceive('lastDownload')->once()->ordered()->andReturn($this->transactionList(80, null, []));

        self::assertSame(0, $this->service->getAndSafeFreshPaymentsFromBank($this->event));

        self::assertSame(80, $this->reloadedCursor());
    }

    public function testMismatchSavesDownloadRewindsAndKeepsCursor(): void
    {
        $this->setCursor(100);
        $this->fioRead->shouldReceive('lastDownload')->once()->ordered()->andReturn(
            $this->transactionList(105, 107, [[106, 500.0]]),
        );
        $this->fioRead->shouldReceive('setLastId')->with(100)->once()->ordered()->andReturn($this->response(200));
        $this->logger->shouldReceive('warning')->once();

        self::assertSame(1, $this->service->getAndSafeFreshPaymentsFromBank($this->event));

        self::assertSame(['106'], $this->storedBankIds());
        self::assertSame(100, $this->reloadedCursor());
    }

    public function testMismatchRewindFailureKeepsPaymentsAndCursor(): void
    {
        $this->setCursor(100);
        $this->fioRead->shouldReceive('lastDownload')->once()->ordered()->andReturn(
            $this->transactionList(105, 107, [[106, 500.0]]),
        );
        $this->fioRead->shouldReceive('setLastId')->with(100)->once()->ordered()->andThrow(new ServiceUnavailable());
        $this->logger->shouldReceive('warning')->once();

        try {
            $this->service->getAndSafeFreshPaymentsFromBank($this->event);
            self::fail('expected ServiceUnavailable');
        } catch (ServiceUnavailable) {
        }

        self::assertSame(['106'], $this->storedBankIds());
        self::assertSame(100, $this->reloadedCursor());
    }

    public function testMismatchRewindRefusedKeepsPaymentsAndCursor(): void
    {
        $this->setCursor(100);
        $this->fioRead->shouldReceive('lastDownload')->once()->ordered()->andReturn(
            $this->transactionList(105, 107, [[106, 500.0]]),
        );
        $this->fioRead->shouldReceive('setLastId')->with(100)->once()->ordered()->andReturn($this->response(404));
        $this->logger->shouldReceive('warning')->once();

        try {
            $this->service->getAndSafeFreshPaymentsFromBank($this->event);
            self::fail('expected ServiceUnavailable');
        } catch (ServiceUnavailable) {
        }

        self::assertSame(['106'], $this->storedBankIds());
        self::assertSame(100, $this->reloadedCursor());
    }

    public function testBootstrapRewindRefusedDownloadsNothingAndKeepsCursorNull(): void
    {
        $this->storeBankPayment('90');
        $this->fioRead->shouldReceive('setLastId')->with(90)->once()->andReturn($this->response(404));
        $this->fioRead->shouldNotReceive('lastDownload');

        try {
            $this->service->getAndSafeFreshPaymentsFromBank($this->event);
            self::fail('expected ServiceUnavailable');
        } catch (ServiceUnavailable) {
        }

        self::assertNull($this->reloadedCursor());
    }

    public function testInterruptedRunIsRecoveredWithoutLossOrDuplicates(): void
    {
        $this->setCursor(100);
        /** @var ArrayObject<int, int> $moveIds */
        $moveIds = new ArrayObject([101, 102]);
        $bank = $this->statefulFioRead(100, $moveIds);
        $service = new FioBankPaymentService(
            $this->bankPaymentRepository,
            $this->eventRepository,
            Mockery::mock(FioBankReaderFactory::class, ['getFioRead' => $bank]),
            Mockery::mock(LoggerInterface::class, ['warning' => null]),
        );

        $bank->lastDownload();
        $moveIds[] = 103;

        self::assertSame(1, $service->getAndSafeFreshPaymentsFromBank($this->event));
        self::assertSame(['103'], $this->storedBankIds());
        self::assertSame(100, $this->reloadedCursor());

        self::assertSame(2, $service->getAndSafeFreshPaymentsFromBank($this->event));
        self::assertSame(['101', '102', '103'], $this->storedBankIds());
        self::assertSame(103, $this->reloadedCursor());
    }

    /**
     * @param list<array{int, float}> $transactions move ID and amount
     */
    private function transactionList(?int $idLastDownload, ?int $idTo, array $transactions): TransactionList
    {
        $items = [];
        foreach ($transactions as [$moveId, $amount]) {
            $items[] = [
                'column0' => ['value' => '2026-10-01+0200'],
                'column1' => ['value' => $amount],
                'column22' => ['value' => $moveId],
            ];
        }

        $json = json_encode([
            'info' => ['idLastDownload' => $idLastDownload, 'idTo' => $idTo],
            'transactionList' => ['transaction' => $items],
        ], JSON_THROW_ON_ERROR);

        $response = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
        self::assertInstanceOf(stdClass::class, $response);

        return new TransactionList($response);
    }

    /**
     * @param ArrayObject<int, int> $moveIds incomes of 500.0, ascending
     */
    private function statefulFioRead(int $breakpoint, ArrayObject $moveIds): FioRead&MockInterface
    {
        $fioRead = Mockery::mock(FioRead::class);
        $fioRead->shouldReceive('lastDownload')->andReturnUsing(
            function () use (&$breakpoint, $moveIds): TransactionList {
                $fresh = array_values(array_filter(
                    $moveIds->getArrayCopy(),
                    fn (int $moveId): bool => $moveId > $breakpoint,
                ));
                $idLastDownload = $breakpoint;
                $idTo = $fresh === [] ? null : max($fresh);
                $breakpoint = $idTo ?? $breakpoint;

                return $this->transactionList(
                    $idLastDownload,
                    $idTo,
                    array_map(fn (int $moveId): array => [$moveId, 500.0], $fresh),
                );
            },
        );
        $fioRead->shouldReceive('setLastId')->andReturnUsing(
            function (int $moveId) use (&$breakpoint): ResponseInterface {
                $breakpoint = $moveId;

                return new Response(200);
            },
        );

        return $fioRead;
    }

    private function response(int $status): ResponseInterface
    {
        return new Response($status);
    }

    private function setCursor(int $moveId): void
    {
        $this->event->bankLastMoveId = $moveId;
        $this->eventRepository->persist($this->event);
    }

    private function reloadedCursor(): ?int
    {
        return $this->eventRepository->get($this->event->id)->bankLastMoveId;
    }

    private function storeBankPayment(string $bankId): void
    {
        $bankPayment = new BankPayment();
        $bankPayment->event = $this->event;
        $bankPayment->bankId = $bankId;
        $bankPayment->status = BankPayment::STATUS_FRESH;
        $this->bankPaymentRepository->persist($bankPayment);
    }

    /**
     * @return list<string>
     */
    private function storedBankIds(): array
    {
        $bankIds = [];
        foreach ($this->bankPaymentRepository->findBy(['event' => $this->event]) as $bankPayment) {
            $bankIds[] = (string)$bankPayment->bankId;
        }
        sort($bankIds);

        return $bankIds;
    }
}
