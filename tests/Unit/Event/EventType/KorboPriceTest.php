<?php

declare(strict_types=1);

namespace Tests\Unit\Event\EventType;

use kissj\Application\DateTimeUtils;
use kissj\Event\EventType\Korbo\EventTypeKorbo;
use kissj\Participant\Participant;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\AppTestCase;

class KorboPriceTest extends AppTestCase
{
    /**
     * @return iterable<string, array{?string, string, int}>
     */
    public static function priceProvider(): iterable
    {
        return [
            'missing close date stays in low tier' => [null, Participant::SCARF_NO, 1000],
            'close before low price end stays in low tier' => ['2026-08-31 23:59:59', Participant::SCARF_NO, 1000],
            'close after low price end adds buffer' => ['2026-09-01 00:00:00', Participant::SCARF_NO, 1150],
            'scarf adds scarf price' => [null, Participant::SCARF_YES, 1150],
            'late tier and scarf stack' => ['2026-09-01 00:00:00', Participant::SCARF_YES, 1300],
        ];
    }

    #[DataProvider('priceProvider')]
    public function testPrice(?string $closeDate, string $scarf, int $expectedPrice): void
    {
        $ist = $this->createIstWithCloseDate($closeDate, $scarf);

        self::assertSame($expectedPrice, (new EventTypeKorbo())->getPrice($ist));
    }

    private function createIstWithCloseDate(?string $closeDate, string $scarf): Participant
    {
        $app = $this->getTestApp();
        $ist = $this->createOpenIst($app->getContainer(), 'Korbo', 'Tester');
        $ist->registrationCloseDate = $closeDate === null ? null : DateTimeUtils::getDateTime($closeDate);
        $ist->scarf = $scarf;
        $ist->getUserButNotNull()->event->defaultPrice = 1000;

        return $ist;
    }
}
