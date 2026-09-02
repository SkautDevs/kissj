<?php

declare(strict_types=1);

namespace Tests\Unit\Event\EventType;

use kissj\Event\EventType\Navigamus\EventTypeNavigamus;
use PHPUnit\Framework\TestCase;

class EventTypeNavigamusTest extends TestCase
{
    public function testUsesNavigamus27Stylesheet(): void
    {
        self::assertSame(
            'eventSpecificCss/stylesNavigamus27.css',
            (new EventTypeNavigamus())->getStylesheetNameWithoutLeadingSlash(),
        );
    }

    // stylesheet assets are referenced by string only, so a missing file would surface as a broken skin
    public function testStylesheetAssetReferencesExist(): void
    {
        $stylesheet = (string) file_get_contents(
            __DIR__ . '/../../../../public/' . (new EventTypeNavigamus())->getStylesheetNameWithoutLeadingSlash(),
        );
        preg_match_all("~url\\('(/[^']+)'\\)~", $stylesheet, $matches);

        self::assertNotEmpty($matches[1]);
        foreach ($matches[1] as $assetPath) {
            self::assertFileExists(__DIR__ . '/../../../../public' . $assetPath);
        }
    }
}
