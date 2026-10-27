<?php

declare(strict_types=1);

namespace Tests\Unit\Event\EventType;

use kissj\Event\EventType\Cej\EventTypeCej;
use kissj\Event\EventType\Obrok\EventTypeObrok;
use kissj\Participant\Guest\Guest;
use kissj\Participant\ParticipantRole;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Payment\Payment;
use kissj\Payment\PaymentStatus;
use kissj\User\User;
use kissj\User\UserStatus;
use PHPUnit\Framework\TestCase;

class ReceiptVisibilityTest extends TestCase
{
    public function testPaidGuestWithPaidPaymentSeesReceipt(): void
    {
        $guest = new class (['role' => ParticipantRole::Guest]) extends Guest {
            public function getUserButNotNull(): User
            {
                return ReceiptVisibilityTest::paidUser();
            }

            /**
             * @return list<Payment>
             */
            public function getPayments(): array
            {
                return [ReceiptVisibilityTest::payment('500', 'paid')];
            }
        };

        self::assertTrue((new EventTypeObrok())->showReceiptToParticipant($guest));
    }

    public function testPaidPatrolLeaderWithOnlyZeroPriceDoesNotSeeReceipt(): void
    {
        $leader = $this->createPatrolLeader([self::payment('0', 'paid')]);

        self::assertFalse((new EventTypeObrok())->showReceiptToParticipant($leader));
    }

    public function testPaidPatrolLeaderWithNonZeroPriceSeesReceipt(): void
    {
        $leader = $this->createPatrolLeader([self::payment('1500', 'paid')]);

        self::assertTrue((new EventTypeObrok())->showReceiptToParticipant($leader));
    }

    public function testNonZeroPaidPaymentsExcludeZeroPriceOnes(): void
    {
        $leader = $this->createPatrolLeader([self::payment('0', 'paid'), self::payment('1500', 'paid')]);

        $payments = $leader->getPaidPaymentsWithNonZeroPrice();

        self::assertCount(1, $payments);
        self::assertSame('1500', $payments[0]->price);
    }

    public function testWaitingPaymentOnlyDoesNotShowReceipt(): void
    {
        $leader = $this->createPatrolLeader([self::payment('1500', 'waiting')]);

        self::assertFalse((new EventTypeObrok())->showReceiptToParticipant($leader));
    }

    public function testCejPaidLeaderFromCzechiaSeesReceipt(): void
    {
        $leader = $this->createPatrolLeader([self::payment('1500', 'paid')], 'detail.contingent.czechia');

        self::assertTrue((new EventTypeCej())->showReceiptToParticipant($leader));
    }

    public function testCejPaidLeaderFromOtherContingentDoesNotSeeReceipt(): void
    {
        $leader = $this->createPatrolLeader([self::payment('1500', 'paid')], 'detail.contingent.slovakia');

        self::assertFalse((new EventTypeCej())->showReceiptToParticipant($leader));
    }

    public static function paidUser(): User
    {
        $user = new User();
        $user->status = UserStatus::Paid;

        return $user;
    }

    public static function payment(string $price, string $status): Payment
    {
        return new Payment(['price' => $price, 'status' => PaymentStatus::from($status)]);
    }

    /**
     * @param list<Payment> $payments
     */
    private function createPatrolLeader(array $payments, ?string $contingent = null): PatrolLeader
    {
        return new class ($payments, $contingent) extends PatrolLeader {
            /**
             * @param list<Payment> $payments
             */
            public function __construct(private readonly array $payments, ?string $contingent)
            {
                parent::__construct(['role' => ParticipantRole::PatrolLeader, 'contingent' => $contingent]);
            }

            public function getUserButNotNull(): User
            {
                return ReceiptVisibilityTest::paidUser();
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
