<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\EventRepository;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeSection;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\AppTestCase;

class ProgrammeExclusivityTest extends AppTestCase
{
    #[DataProvider('exclusivityProvider')]
    public function testExclusivity(bool $isSameSection, bool $isExclusive, string $startB, bool $expected): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $event = $this->getSmallTestEvent($this->getService($app, EventRepository::class));
        $sectionA = $this->createProgrammeSection($container, $event, 'section A', $isExclusive);
        $sectionB = $isSameSection
            ? $sectionA
            : $this->createProgrammeSection($container, $event, 'section B', $isExclusive);

        $a = $this->createProgramme('2027-06-05 08:00', '2027-06-05 10:00', $sectionA);
        $b = $this->createProgramme($startB, '2027-06-05 12:00', $sectionB);

        // isExclusiveWith is symmetric, so both directions must agree
        self::assertSame($expected, $a->isExclusiveWith($b));
        self::assertSame($expected, $b->isExclusiveWith($a));
    }

    /**
     * @return array<string, array{bool, bool, string, bool}>
     */
    public static function exclusivityProvider(): array
    {
        return [
            'same exclusive section overlapping is exclusive' => [true, true, '2027-06-05 09:00', true],
            'same exclusive section touching borders is not exclusive' => [true, true, '2027-06-05 10:00', false],
            'same non-exclusive section overlapping is not exclusive' => [true, false, '2027-06-05 09:00', false],
            'different exclusive sections overlapping is not exclusive' => [false, true, '2027-06-05 09:00', false],
        ];
    }

    private function createProgramme(string $start, string $end, ProgrammeSection $section): Programme
    {
        $programme = new Programme();
        $programme->start = DateTimeUtils::getDateTime($start);
        $programme->end = DateTimeUtils::getDateTime($end);
        $programme->section = $section;

        return $programme;
    }
}
