<?php

declare(strict_types=1);

namespace Tests\Unit\PdfGenerator;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventType\Obrok\EventTypeObrok;
use kissj\Participant\Guest\Guest;
use kissj\Participant\ParticipantRole;
use kissj\Payment\Payment;
use kissj\Payment\PaymentStatus;
use kissj\Payment\QrCodeService;
use kissj\PdfGenerator\mPdfGenerator;
use kissj\Telemetry\Metrics;
use kissj\User\User;
use Mpdf\Mpdf;
use PHPUnit\Framework\TestCase;
use Slim\Views\Twig;

class MPdfGeneratorReceiptTest extends TestCase
{
    public function testReceiptsContainOnlyNonZeroPaidPayments(): void
    {
        $zeroPayment = new Payment(['id' => 1, 'price' => '0', 'status' => PaymentStatus::Paid, 'paidAt' => null]);
        $paidPayment = new Payment(['id' => 2, 'price' => '1500', 'status' => PaymentStatus::Paid, 'paidAt' => null]);
        $waitingPayment = new Payment(
            ['id' => 3, 'price' => '700', 'status' => PaymentStatus::Waiting, 'paidAt' => null],
        );
        $guest = $this->createGuest([$zeroPayment, $paidPayment, $waitingPayment]);

        $capturedData = [];
        $twig = self::createStub(Twig::class);
        $twig->method('fetch')->willReturnCallback(
            function (string $template, array $data) use (&$capturedData): string {
                $capturedData = $data;

                return '';
            },
        );
        $mpdf = self::createStub(Mpdf::class);
        $mpdf->method('Output')->willReturn('pdf');

        $generator = new mPdfGenerator(
            $mpdf,
            $twig,
            self::createStub(Metrics::class),
            self::createStub(QrCodeService::class),
        );
        $generator->generatePdfReceipt($guest, 'pdf/receipt.twig');

        $receipts = $capturedData['receipts'];
        self::assertIsArray($receipts);
        self::assertSame([$paidPayment], array_column($receipts, 'payment'));
    }

    /**
     * @param list<Payment> $payments
     */
    private function createGuest(array $payments): Guest
    {
        $event = new class () extends Event {
            public function getEventType(): EventTypeObrok
            {
                return new EventTypeObrok();
            }
        };
        $event->slug = 'obrok';
        $event->startDay = DateTimeUtils::getDateTime('2027-07-01');
        $event->endDay = DateTimeUtils::getDateTime('2027-07-08');
        $user = self::createStub(User::class);
        $user->method('__get')->willReturn($event);

        return new class ($payments, $user) extends Guest {
            /**
             * @param list<Payment> $payments
             */
            public function __construct(private readonly array $payments, private readonly User $user)
            {
                parent::__construct(['id' => 7, 'role' => ParticipantRole::Guest]);
            }

            public function getUserButNotNull(): User
            {
                return $this->user;
            }

            /**
             * @return list<Payment>
             */
            public function getPayments(): array
            {
                return $this->payments;
            }
        };
    }
}
