<?php

declare(strict_types=1);

namespace Tests\Unit\Mailer;

use kissj\Event\EventType\Aqua\EventTypeAqua;
use kissj\Event\EventType\Cej\EventTypeCej;
use kissj\Event\EventType\EventType;
use kissj\Event\EventType\EventTypeDefault;
use kissj\Event\EventType\Jj\EventTypeJj;
use kissj\Event\EventType\Korbo\EventTypeKorbo;
use kissj\Event\EventType\Miquik\EventTypeMiquik;
use kissj\Event\EventType\Navigamus\EventTypeNavigamus;
use kissj\Event\EventType\Nsj\EventTypeNsj;
use kissj\Event\EventType\Obrok\EventTypeObrok;
use kissj\Event\EventType\Ospz\EventTypeOspz;
use kissj\Event\EventType\Wsj\EventTypeWsj;
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
     * The bug this guards: an event overrides a neutral key with its own text, but the
     * base catalogue still supplies the `.man`/`.woman` variants. `transGendered` then
     * serves that stale base gendered text to participants with a gender set — silently
     * ignoring the event override. Gender is only populated for Skautis events, so only
     * those can hit it; an override whose text equals the base is harmless (nothing to
     * shadow). Any Skautis event that overrides a gendered-family neutral key with
     * different text must therefore also override both gendered variants.
     */
    public function testSkautisEventGenderOverridesStayConsistent(): void
    {
        $violations = [];

        foreach (self::skautisEventTypes() as $slug => $eventType) {
            foreach ($eventType->getTranslationFilePaths() as $locale => $path) {
                $baseFile = self::PROJECT_ROOT . 'src/Templates/' . $locale . '.yaml';
                if (!is_file($baseFile)) {
                    continue;
                }
                $base = self::loadMessagesFromFile($baseFile);
                $override = self::loadMessagesFromFile($path);

                foreach ($base as $key => $_value) {
                    if (!str_ends_with($key, '.man')) {
                        continue;
                    }
                    $baseKey = substr($key, 0, -strlen('.man'));

                    if (!array_key_exists($baseKey, $override)) {
                        continue;
                    }
                    if (($base[$baseKey] ?? null) === $override[$baseKey]) {
                        continue;
                    }

                    foreach (['.man', '.woman'] as $suffix) {
                        if (!array_key_exists($baseKey . $suffix, $override)) {
                            $violations[] = sprintf(
                                '%s (%s) overrides %s but not %s%s — base gendered text leaks for gendered participants',
                                $slug,
                                $locale,
                                $baseKey,
                                $baseKey,
                                $suffix,
                            );
                        }
                    }
                }
            }
        }

        self::assertSame(
            [],
            $violations,
            "Skautis event gender-override gaps:\n  - " . implode("\n  - ", $violations),
        );
    }

    /**
     * @return array<string, EventType>
     */
    private static function skautisEventTypes(): array
    {
        $eventTypes = [
            'aqua' => new EventTypeAqua(),
            'cej' => new EventTypeCej(),
            'jj' => new EventTypeJj(),
            'korbo' => new EventTypeKorbo(),
            'miquik' => new EventTypeMiquik(),
            'navigamus' => new EventTypeNavigamus(),
            'nsj' => new EventTypeNsj(),
            'obrok' => new EventTypeObrok(),
            'ospz' => new EventTypeOspz(),
            'wsj' => new EventTypeWsj(),
            'default' => new EventTypeDefault(),
        ];

        return array_filter($eventTypes, static fn (EventType $eventType): bool => $eventType->isLoginSkautisAllowed());
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
            'obrok payment-successful enjoy' => ['src/Event/EventType/Obrok/cs_obrok.yaml', 'email.payment-successful.enjoy'],
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
        return self::loadMessagesFromFile(self::PROJECT_ROOT . $relativeYamlPath);
    }

    /**
     * @return array<string, string>
     */
    private static function loadMessagesFromFile(string $absoluteYamlPath): array
    {
        $translator = new Translator('cs');
        $translator->addLoader('yaml', new YamlFileLoader());
        $translator->addResource('yaml', $absoluteYamlPath, 'cs');

        /** @var array<string, string> $messages */
        $messages = $translator->getCatalogue('cs')->all('messages');

        return $messages;
    }
}
