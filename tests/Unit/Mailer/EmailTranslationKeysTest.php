<?php

declare(strict_types=1);

namespace Tests\Unit\Mailer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;

class EmailTranslationKeysTest extends TestCase
{
    private const string PROJECT_ROOT = __DIR__ . '/../../../';

    #[DataProvider('genderedKeyProvider')]
    public function testGenderedKeyHasAllVariants(string $yamlPath, string $dottedKey): void
    {
        $messages = self::loadMessages($yamlPath);

        self::assertArrayHasKey(
            $dottedKey,
            $messages,
            "missing base key {$dottedKey} in {$yamlPath}",
        );
        self::assertArrayHasKey(
            $dottedKey . '.man',
            $messages,
            "missing {$dottedKey}.man in {$yamlPath}",
        );
        self::assertArrayHasKey(
            $dottedKey . '.woman',
            $messages,
            "missing {$dottedKey}.woman in {$yamlPath}",
        );

        self::assertDoesNotMatchRegularExpression(
            '#\p{L}/\p{L}\b#u',
            $messages[$dottedKey],
            "base form must be gender-neutral (no slashed inflection) in {$dottedKey}",
        );
    }

    /**
     * Any `.man` or `.woman` key must have a sibling `.woman`/`.man` and a base form.
     * Catches half-translated overrides regardless of which yaml introduced them.
     */
    #[DataProvider('allYamlPathProvider')]
    public function testNoOrphanGenderVariants(string $yamlPath): void
    {
        $messages = self::loadMessages($yamlPath);
        $violations = [];

        foreach ($messages as $key => $_value) {
            if (!str_ends_with($key, '.man') && !str_ends_with($key, '.woman')) {
                continue;
            }
            $suffix = str_ends_with($key, '.man') ? '.man' : '.woman';
            $base = substr($key, 0, -strlen($suffix));
            $otherSuffix = $suffix === '.man' ? '.woman' : '.man';

            if (!array_key_exists($base, $messages)) {
                $violations[] = "gendered key {$key} has no base form {$base}";
                continue;
            }
            if (!array_key_exists($base . $otherSuffix, $messages)) {
                $violations[] = "{$key} present but {$base}{$otherSuffix} missing";
            }
            if (preg_match('#\p{L}/\p{L}\b#u', $messages[$base]) === 1) {
                $violations[] = "base form of {$base} must be gender-neutral (no slashed inflection)";
            }
        }

        self::assertSame(
            [],
            $violations,
            "{$yamlPath} has gender-variant inconsistencies:\n  - " . implode("\n  - ", $violations),
        );
    }

    /**
     * Gender suffix lookup falls back to the unsuffixed key in the same locale.
     * That fallback only works if every locale's base catalogue defines the unsuffixed key.
     */
    public function testCrossLocaleBaseFormCoverage(): void
    {
        $csMessages = self::loadMessages('src/Templates/cs.yaml');
        $enMessages = self::loadMessages('src/Templates/en.yaml');
        $skMessages = self::loadMessages('src/Templates/sk.yaml');

        foreach ($csMessages as $key => $_value) {
            if (!str_ends_with($key, '.man') && !str_ends_with($key, '.woman')) {
                continue;
            }
            $base = preg_replace('/\.(man|woman)$/', '', $key);
            self::assertNotNull($base);

            self::assertArrayHasKey(
                $base,
                $enMessages,
                "en.yaml missing base form {$base} — English users with gender set would fall back to Czech",
            );
            self::assertArrayHasKey(
                $base,
                $skMessages,
                "sk.yaml missing base form {$base} — Slovak users with gender set would fall back to Czech",
            );
        }
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

    /**
     * @return array<string, array{string}>
     */
    public static function allYamlPathProvider(): array
    {
        $cases = [];
        foreach (self::discoverYamlPaths() as $path) {
            $cases[$path] = [$path];
        }
        return $cases;
    }

    /**
     * @return list<string>
     */
    private static function discoverYamlPaths(): array
    {
        $templates = glob(self::PROJECT_ROOT . 'src/Templates/*.yaml');
        $eventTypes = glob(self::PROJECT_ROOT . 'src/Event/EventType/*/*.yaml');
        $paths = array_merge($templates === false ? [] : $templates, $eventTypes === false ? [] : $eventTypes);

        $projectRoot = realpath(self::PROJECT_ROOT);
        if ($projectRoot === false) {
            self::fail('cannot resolve project root');
        }
        $rootLen = strlen($projectRoot) + 1;

        return array_values(array_map(
            static function (string $absolute) use ($rootLen): string {
                $resolved = realpath($absolute);
                if ($resolved === false) {
                    self::fail("cannot resolve yaml path {$absolute}");
                }
                return substr($resolved, $rootLen);
            },
            $paths,
        ));
    }

    /**
     * @return array<string, string>
     */
    private static function loadMessages(string $relativeYamlPath): array
    {
        $translator = new Translator('cs');
        $translator->addLoader('yaml', new YamlFileLoader());
        $translator->addResource('yaml', self::PROJECT_ROOT . $relativeYamlPath, 'cs');

        /** @var array<string, string> $messages */
        $messages = $translator->getCatalogue('cs')->all('messages');

        return $messages;
    }
}
