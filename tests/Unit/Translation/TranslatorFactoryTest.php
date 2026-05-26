<?php

declare(strict_types=1);

namespace Tests\Unit\Translation;

use kissj\Event\EventType\EventType;
use kissj\Event\EventType\EventTypeDefault;
use kissj\Translation\TranslatorFactory;
use PHPUnit\Framework\TestCase;

class TranslatorFactoryTest extends TestCase
{
    private string $fixtureDir;

    protected function setUp(): void
    {
        $this->fixtureDir = sys_get_temp_dir() . '/kissj-translation-' . uniqid();
        mkdir($this->fixtureDir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->fixtureDir . '/*') ?: [] as $file) {
            unlink($file);
        }
        rmdir($this->fixtureDir);
    }

    public function testBaseTranslatorTranslatesBaseKey(): void
    {
        $factory = new TranslatorFactory('cs', null, false);

        $translator = $factory->createBase();

        self::assertSame('Odeslat registraci', $translator->trans('closeRegistration.title'));
    }

    public function testFallsBackToDefaultLocaleWhenKeyMissing(): void
    {
        $factory = new TranslatorFactory('cs', null, false);
        $translator = $factory->createBase();
        $translator->setLocale('en');

        // 'closeRegistration.title' is in both en.yaml and cs.yaml, so en wins
        self::assertSame('Send registration', $translator->trans('closeRegistration.title'));
    }

    public function testEventTypeWithoutOverridesMatchesBase(): void
    {
        $factory = new TranslatorFactory('cs', null, false);

        $translator = $factory->createForEventType(new EventTypeDefault());

        self::assertSame('Odeslat registraci', $translator->trans('closeRegistration.title'));
    }

    public function testEventTypeOverridesAreLoaded(): void
    {
        file_put_contents(
            $this->fixtureDir . '/cs_test.yaml',
            "fixture:\n    key: \"český override\"\n",
        );

        $factory = new TranslatorFactory('cs', null, false);
        $eventType = $this->makeEventType([
            'cs' => $this->fixtureDir . '/cs_test.yaml',
        ]);

        $translator = $factory->createForEventType($eventType);

        self::assertSame('český override', $translator->trans('fixture.key'));
    }

    public function testBaseTranslatorDoesNotSeeEventOverrides(): void
    {
        file_put_contents(
            $this->fixtureDir . '/cs_test.yaml',
            "leaky:\n    key: \"should not leak\"\n",
        );

        $factory = new TranslatorFactory('cs', null, false);
        $eventType = $this->makeEventType(['cs' => $this->fixtureDir . '/cs_test.yaml']);

        $factory->createForEventType($eventType);
        $base = $factory->createBase();

        // Untranslated keys resolve to the id itself — confirms isolation.
        self::assertSame('leaky.key', $base->trans('leaky.key'));
    }

    public function testFactoryReturnsIndependentInstancesPerCall(): void
    {
        $factory = new TranslatorFactory('cs', null, false);

        $first = $factory->createBase();
        $second = $factory->createBase();

        self::assertNotSame($first, $second);
    }

    /**
     * @param array<string, string> $paths
     */
    private function makeEventType(array $paths): EventType
    {
        return new class ($paths) extends EventType {
            /**
             * @param array<string, string> $paths
             */
            public function __construct(private readonly array $paths)
            {
            }

            public function getTranslationFilePaths(): array
            {
                return $this->paths;
            }
        };
    }
}
