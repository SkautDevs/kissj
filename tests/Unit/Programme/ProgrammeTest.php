<?php

declare(strict_types=1);

namespace Tests\Unit\Programme;

use kissj\Application\DateTimeUtils;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeSection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ProgrammeTest extends TestCase
{
    public function testSectionLabelAppendsSubtitleOnlyWhenSet(): void
    {
        $section = new ProgrammeSection();
        $section->name = 'Vapro';
        $section->subtitle = null;
        self::assertSame('Vapro', $section->getLabel());

        $section->subtitle = '1. blok';
        self::assertSame('Vapro (1. blok)', $section->getLabel());
    }

    #[DataProvider('overlapProvider')]
    public function testOverlap(string $startA, string $endA, string $startB, string $endB, bool $expected): void
    {
        $a = $this->createProgramme($startA, $endA);
        $b = $this->createProgramme($startB, $endB);

        // overlapsWith is symmetric, so both directions must agree
        self::assertSame($expected, $a->overlapsWith($b));
        self::assertSame($expected, $b->overlapsWith($a));
    }

    /**
     * @return array<string, array{string, string, string, string, bool}>
     */
    public static function overlapProvider(): array
    {
        return [
            'touching borders do not overlap' =>
                ['2027-06-05 08:00', '2027-06-05 10:00', '2027-06-05 10:00', '2027-06-05 12:00', false],
            'partial overlap' =>
                ['2027-06-05 08:00', '2027-06-05 10:01', '2027-06-05 10:00', '2027-06-05 12:00', true],
            'containment overlaps' =>
                ['2027-06-05 00:00', '2027-06-06 00:00', '2027-06-05 10:00', '2027-06-05 12:00', true],
        ];
    }

    private function createProgramme(string $start, string $end): Programme
    {
        $programme = new Programme();
        $programme->start = DateTimeUtils::getDateTime($start);
        $programme->end = DateTimeUtils::getDateTime($end);

        return $programme;
    }
}
