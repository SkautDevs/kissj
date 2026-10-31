<?php

declare(strict_types=1);

namespace Tests\Unit\Event\EventType;

use kissj\Event\EventType\EventType;
use kissj\Event\EventType\EventTypeDefault;
use kissj\Event\EventType\Navigamus\EventTypeNavigamus;
use kissj\Event\EventType\Obrok\EventTypeObrok;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class PositionOptionsTest extends TestCase
{
    /**
     * @return array<string, array{EventType}>
     */
    public static function eventTypes(): array
    {
        return [
            'default' => [new EventTypeDefault()],
            'obrok' => [new EventTypeObrok()],
            'navigamus' => [new EventTypeNavigamus()],
        ];
    }

    #[DataProvider('eventTypes')]
    public function testPositionListsEndWithOther(EventType $eventType): void
    {
        $options = $eventType->getPositionOptions();

        self::assertSame('detail.position.other', $options[count($options) - 1]);
    }

    public function testOtherPositionCopy(): void
    {
        $expected = [
            'cs' => 'jiné - prosím upřesni v dovednostech',
            'sk' => 'iné - prosím upresni v zručnostiach',
            'en' => 'other - please specify in skills',
        ];

        foreach ($expected as $locale => $copy) {
            $translations = Yaml::parseFile(__DIR__ . '/../../../../src/Templates/' . $locale . '.yaml');
            self::assertIsArray($translations);
            self::assertIsArray($translations['detail']);
            self::assertIsArray($translations['detail']['position']);
            self::assertSame($copy, $translations['detail']['position']['other']);
        }

        $navigamus = Yaml::parseFile(__DIR__ . '/../../../../src/Event/EventType/Navigamus/cs_navigamus.yaml');
        self::assertIsArray($navigamus);
        self::assertIsArray($navigamus['detail']);
        self::assertIsArray($navigamus['detail']['position']);
        self::assertSame('jiné - prosím upřesni v poznámce', $navigamus['detail']['position']['other']);
    }
}
