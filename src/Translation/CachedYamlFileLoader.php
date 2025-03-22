<?php

declare(strict_types=1);

namespace kissj\Translation;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Contracts\Cache\ItemInterface;

class CachedYamlFileLoader extends YamlFileLoader
{
    public function __construct(
        private readonly ?string $cacheDir,
        private readonly ?string $eventType = null,
    ) {
    }

    public function load(mixed $resource, string $locale, string $domain = 'messages'): MessageCatalogue
    {
        if ($this->cacheDir === null) {
            return parent::load($resource, $locale, $domain);
        }

        if (!is_string($resource)) {
            return parent::load($resource, $locale, $domain);
        }

        $cacheKey = $this->getCacheKey($resource, $locale, $domain);
        $cache = new FilesystemAdapter('translations', 0, $this->cacheDir); // 0 means no TTL

        return $cache->get($cacheKey, function (ItemInterface $item) use ($resource, $locale, $domain) {
            $catalogue = parent::load($resource, $locale, $domain);
            
            // Cache the actual translations from the catalogue
            $translations = $catalogue->all($domain);
            $item->set($translations);
            
            return $catalogue;
        });
    }

    private function getCacheKey(string $resource, string $locale, string $domain): string
    {
        $eventTypePrefix = $this->eventType !== null ? str_replace('\\', '_', $this->eventType) . '_' : '';
        $fileHash = md5_file($resource);
        return "{$eventTypePrefix}{$locale}_{$domain}_{$fileHash}";
    }
} 