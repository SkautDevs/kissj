<?php

declare(strict_types=1);

namespace Tests\Unit\Participant;

use kissj\Participant\TshirtService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class TshirtServiceTest extends TestCase
{
    public function testDisplayUsesTranslationWhenAvailable(): void
    {
        $service = new TshirtService($this->makeTranslator(['detail.tshirtXL' => 'XL (větší)']));

        self::assertSame('XL (větší)', $service->displaySize('detail.tshirtXL'));
    }

    public function testDisplayStripsKnownPrefixWhenTranslationMissing(): void
    {
        $service = new TshirtService($this->makeTranslator([]));

        self::assertSame('male', $service->displayShape('detail.tshirtGenderMale'));
        self::assertSame('XL', $service->displaySize('detail.tshirtXL'));
    }

    public function testDisplayPassesUnknownValuesThrough(): void
    {
        $service = new TshirtService($this->makeTranslator([]));

        self::assertSame('whatever', $service->displaySize('whatever'));
        self::assertNull($service->displaySize(null));
        self::assertNull($service->displayShape(''));
    }

    /**
     * @param array<string, string> $translations
     */
    private function makeTranslator(array $translations): TranslatorInterface
    {
        return new class ($translations) implements TranslatorInterface {
            /**
             * @param array<string, string> $translations
             */
            public function __construct(private readonly array $translations)
            {
            }

            /**
             * @param array<mixed> $parameters
             */
            public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
            {
                return $this->translations[$id] ?? $id;
            }

            public function getLocale(): string
            {
                return 'cs';
            }
        };
    }
}
