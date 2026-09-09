<?php

declare(strict_types=1);

namespace Tests\Unit\Event\EventType;

use kissj\Event\EventType\EventType;
use kissj\Event\EventType\EventTypeDefault;
use kissj\Event\EventType\Korbo\EventTypeKorbo;
use kissj\Event\EventType\Navigamus\EventTypeNavigamus;
use kissj\Event\EventType\Obrok\EventTypeObrok;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EmailStylesheetTest extends TestCase
{
    public function testBaseEventTypeReturnsNoEmailStylesheet(): void
    {
        self::assertNull((new EventTypeDefault())->getEmailStylesheetNameWithoutLeadingSlash());
    }

    #[DataProvider('provideEventTypesWithEmailStylesheet')]
    public function testReturnsEmailStylesheetPath(EventType $eventType, string $expectedPath): void
    {
        self::assertSame($expectedPath, $eventType->getEmailStylesheetNameWithoutLeadingSlash());
    }

    // Mailer loads this by name off the filesystem, so a typo would only surface as an unstyled email
    #[DataProvider('provideEventTypesWithEmailStylesheet')]
    public function testEmailStylesheetFileExists(EventType $eventType, string $expectedPath): void
    {
        // is_file, because assertFileExists also passes for a bare directory
        self::assertTrue(is_file(__DIR__ . '/../../../../public/' . $expectedPath));
    }

    // mail clients do not support custom properties or webfonts - a var() here renders as nothing,
    // which is exactly the failure mode this whole mechanism exists to avoid
    #[DataProvider('provideEventTypesWithEmailStylesheet')]
    public function testEmailStylesheetIsMailClientSafe(EventType $eventType, string $expectedPath): void
    {
        $css = file_get_contents(__DIR__ . '/../../../../public/' . $expectedPath);
        self::assertIsString($css);

        self::assertStringNotContainsString('var(', $css);
        self::assertStringNotContainsString('@font-face', $css);
        self::assertStringNotContainsString('@import', $css);
    }

    /**
     * @return iterable<string, array{EventType, string}>
     */
    public static function provideEventTypesWithEmailStylesheet(): iterable
    {
        yield 'obrok' => [new EventTypeObrok(), 'eventSpecificCss/emailObrok27.css'];
        yield 'navigamus' => [new EventTypeNavigamus(), 'eventSpecificCss/emailNavigamus27.css'];
        yield 'korbo' => [new EventTypeKorbo(), 'eventSpecificCss/emailKorbo.css'];
    }
}
