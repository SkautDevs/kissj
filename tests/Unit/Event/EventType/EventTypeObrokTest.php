<?php

declare(strict_types=1);

namespace Tests\Unit\Event\EventType;

use kissj\Event\EventType\EventTypeDefault;
use kissj\Event\EventType\Obrok\EventTypeObrok;
use PHPUnit\Framework\TestCase;

class EventTypeObrokTest extends TestCase
{
    public function testUsesObrok27Script(): void
    {
        self::assertSame(
            'obrok27/obrok27.js',
            (new EventTypeObrok())->getScriptNameWithoutLeadingSlash(),
        );
    }

    // _layout.twig loads this by name off the filesystem, so a typo would only surface as a 404 in production
    public function testObrok27ScriptFileExists(): void
    {
        // is_file, because assertFileExists also passes for the bare public/ directory
        self::assertTrue(
            is_file(__DIR__ . '/../../../../public/' . (new EventTypeObrok())->getScriptNameWithoutLeadingSlash()),
        );
    }

    public function testMissingCodeAnimationFileExists(): void
    {
        self::assertTrue(is_file(__DIR__ . '/../../../../public/obrok27/hrozeni_prstem.webp'));
    }

    public function testTagSlotListsExactlyTheTagFiles(): void
    {
        $publicDir = __DIR__ . '/../../../../public/';
        $script = file_get_contents($publicDir . (new EventTypeObrok())->getScriptNameWithoutLeadingSlash());
        self::assertIsString($script);
        preg_match_all('/O27tag_\w+\.svg/', $script, $matches);
        $listed = array_unique($matches[0]);
        sort($listed);

        $tagFiles = glob($publicDir . 'obrok27/tags/*.svg');
        self::assertIsArray($tagFiles);
        $onDisk = array_map(basename(...), $tagFiles);
        sort($onDisk);

        self::assertNotEmpty($onDisk);
        self::assertSame($onDisk, $listed);
    }

    public function testShowsTieCodeToIst(): void
    {
        self::assertTrue((new EventTypeObrok())->isTieCodeShownToIst());
    }

    public function testDefaultHidesTieCodeFromIst(): void
    {
        self::assertFalse((new EventTypeDefault())->isTieCodeShownToIst());
    }
}
