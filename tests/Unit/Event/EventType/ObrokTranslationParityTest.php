<?php

declare(strict_types=1);

namespace Tests\Unit\Event\EventType;

use kissj\Event\EventType\Obrok\EventTypeObrok;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Loader\YamlFileLoader;

class ObrokTranslationParityTest extends TestCase
{
    private const string OBROK_DIR = __DIR__ . '/../../../../src/Event/EventType/Obrok/';

    public function testEnglishMirrorsCzechKeys(): void
    {
        self::assertSame(
            $this->loadSortedKeys('cs_obrok.yaml', 'cs'),
            $this->loadSortedKeys('en_obrok.yaml', 'en'),
        );
    }

    public function testEnglishIsOfferedAfterCzech(): void
    {
        self::assertSame(['cs', 'en'], array_keys((new EventTypeObrok())->getLanguages()));
    }

    /**
     * @return list<string>
     */
    private function loadSortedKeys(string $fileName, string $locale): array
    {
        $keys = array_map(
            strval(...),
            array_keys((new YamlFileLoader())->load(self::OBROK_DIR . $fileName, $locale)->all('messages')),
        );
        $genderNeutralKeys = array_values(array_filter(
            $keys,
            static fn (string $key): bool => str_ends_with($key, '.man') === false
                && str_ends_with($key, '.woman') === false,
        ));
        sort($genderNeutralKeys);

        return $genderNeutralKeys;
    }
}
