<?php

declare(strict_types=1);

namespace Tests\Unit\Event\EventType;

use kissj\Event\EventType\EventTypeDefault;
use kissj\Event\EventType\Obrok\EventTypeObrok;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Troop\TroopLeader;
use PHPUnit\Framework\TestCase;

class PaidMailTranslationPrefixTest extends TestCase
{
    public function testDefaultEventUsesBasePrefix(): void
    {
        self::assertSame(
            'email.payment-successful',
            (new EventTypeDefault())->getPaidMailTranslationPrefix(new Ist()),
        );
    }

    public function testObrokIstUsesIstPrefix(): void
    {
        self::assertSame(
            'email.payment-successful-ist',
            (new EventTypeObrok())->getPaidMailTranslationPrefix(new Ist()),
        );
    }

    public function testObrokTroopLeaderUsesBasePrefix(): void
    {
        self::assertSame(
            'email.payment-successful',
            (new EventTypeObrok())->getPaidMailTranslationPrefix(new TroopLeader()),
        );
    }
}
