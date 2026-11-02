<?php

declare(strict_types=1);

namespace kissj\Translation;

use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CurrentTranslator implements TranslatorInterface, LocaleAwareInterface
{
    private TranslatorInterface&LocaleAwareInterface&TranslatorBagInterface $delegate;

    public function __construct(
        private readonly TranslatorFactory $factory
    ) {
    }

    public function setDelegate(TranslatorInterface&LocaleAwareInterface&TranslatorBagInterface $delegate): void
    {
        $this->delegate = $delegate;
    }

    /**
     * @param array<array-key, mixed> $parameters
     */
    public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        return $this->getDelegate()->trans($id, $parameters, $domain, $locale);
    }

    /**
     * @param array<array-key, mixed> $parameters
     */
    public function transGendered(string $key, string $suffix, array $parameters = []): string
    {
        $genderedKey = $key . $suffix;
        if ($suffix !== '' && $this->getDelegate()->getCatalogue()->defines($genderedKey)) {
            return $this->trans($genderedKey, $parameters);
        }

        return $this->trans($key, $parameters);
    }

    public function getLocale(): string
    {
        return $this->getDelegate()->getLocale();
    }

    public function setLocale(string $locale): void
    {
        $this->getDelegate()->setLocale($locale);
    }

    private function getDelegate(): TranslatorInterface&LocaleAwareInterface&TranslatorBagInterface
    {
        if (!isset($this->delegate)) {
            $this->delegate = $this->factory->createBase();
        }

        return $this->delegate;
    }
}
