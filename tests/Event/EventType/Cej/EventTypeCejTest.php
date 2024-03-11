<?php declare(strict_types=1);

namespace Tests\Event\EventType\Cej;

use kissj\Event\EventType\Cej\EventTypeCej;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Participant;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Payment\Payment;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EventTypeCejTest extends TestCase
{
    private EventTypeCej $eventTypeCej;

    public function setUp(): void
    {
        $this->eventTypeCej = new EventTypeCej();
    }

    #[DataProvider('provideGetPrice')]
    public function testTransformPayment(
        int $expectedPrice,
        Participant $participant,
        Payment $payment,
    ): void {
        $this->assertSame($expectedPrice, $this->eventTypeCej->transformPayment($payment, $participant));
    }

    /**
     * @return array<string, array<mixed>>
     */
    public static function provideGetPrice(): array
    {
        self::markTestSkipped('TODO make adding participants work');

        $teamMember = new Participant();
        $teamMember->contingent = EventTypeCej::CONTINGENT_TEAM;

        $ist = new Ist();
        $ist->contingent = EventTypeCej::CONTINGENT_CZECHIA;

        $pps = [];
        for ($i = 0; $i < 9; $i++) {
            $pps[] = new PatrolParticipant();
        }

        $pl = new PatrolLeader();
        $pl->contingent = EventTypeCej::CONTINGENT_CZECHIA;
        $pl->patrolParticipants = $pps;

        return [
            'team member' => [1450, $teamMember],
            'ist' => [2900, $ist],
        ];
    }
}
