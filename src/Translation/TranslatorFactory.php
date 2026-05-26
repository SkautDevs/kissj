<?php

declare(strict_types=1);

namespace kissj\Translation;

use kissj\Event\EventType\EventType;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;

class TranslatorFactory
{
    private const string BASE_CACHE_KEY = '_base';

    /** @var array<string, string> */
    private readonly array $baseResources;

    public function __construct(
        private readonly string $defaultLocale,
        private readonly ?string $cacheDir,
        private readonly bool $debug,
    ) {
        $templatesDir = __DIR__ . '/../Templates';
        $this->baseResources = [
            'cs' => $templatesDir . '/cs.yaml',
            'sk' => $templatesDir . '/sk.yaml',
            'en' => $templatesDir . '/en.yaml',
        ];
    }

    public function createBase(): Translator
    {
        return $this->build([], self::BASE_CACHE_KEY);
    }

    public function createForEventType(EventType $eventType): Translator
    {
        return $this->build($eventType->getTranslationFilePaths(), $eventType::class);
    }

    /**
     * @param array<string, string> $extraResources
     */
    private function build(array $extraResources, string $cacheVaryKey): Translator
    {
        $translator = new Translator(
            $this->defaultLocale,
            null,
            $this->cacheDir,
            $this->debug,
            ['event_type' => $cacheVaryKey],
        );
        $translator->setFallbackLocales([$this->defaultLocale]);
        $translator->addLoader('yaml', new YamlFileLoader());

        foreach ($this->baseResources as $locale => $path) {
            $translator->addResource('yaml', $path, $locale);
        }
        foreach ($extraResources as $locale => $path) {
            $translator->addResource('yaml', $path, $locale);
        }

        return $translator;
    }
}
