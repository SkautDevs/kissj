<?php

declare(strict_types=1);

namespace Tests\Unit\Mailer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;

class EmailTranslationKeysTest extends TestCase
{
    #[DataProvider('genderedKeyProvider')]
    public function testGenderedKeyHasAllVariants(string $yamlPath, string $dottedKey): void
    {
        $translator = new Translator('cs');
        $translator->addLoader('yaml', new YamlFileLoader());
        $translator->addResource('yaml', __DIR__ . '/../../../' . $yamlPath, 'cs');
        $catalogue = $translator->getCatalogue('cs');

        self::assertTrue(
            $catalogue->has($dottedKey),
            "missing base key {$dottedKey} in {$yamlPath}",
        );
        self::assertTrue(
            $catalogue->has($dottedKey . '.man'),
            "missing {$dottedKey}.man in {$yamlPath}",
        );
        self::assertTrue(
            $catalogue->has($dottedKey . '.woman'),
            "missing {$dottedKey}.woman in {$yamlPath}",
        );

        self::assertDoesNotMatchRegularExpression(
            '#\p{L}/\p{L}\b#u',
            $catalogue->get($dottedKey),
            "base form must be gender-neutral (no slashed inflection) in {$dottedKey}",
        );
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function genderedKeyProvider(): array
    {
        return [
            'cs closed successfullySent'     => ['src/Templates/cs.yaml',              'email.closed.successfullySent'],
            'cs denial requirements'         => ['src/Templates/cs.yaml',              'email.denial.requirements'],
            'korbo layout ifQuestion'        => ['src/Event/EventType/Korbo/cs.yaml',  'email.layout.ifQuestion'],
            'korbo closed successfullySent'  => ['src/Event/EventType/Korbo/cs.yaml',  'email.closed.successfullySent'],
            'korbo payment-successful enjoy' => ['src/Event/EventType/Korbo/cs.yaml',  'email.payment-successful.enjoy'],
        ];
    }
}
