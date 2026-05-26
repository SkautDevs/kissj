<?php

declare(strict_types=1);

namespace kissj\Translation;

use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CurrentTranslator implements TranslatorInterface, LocaleAwareInterface
{
    private TranslatorInterface&LocaleAwareInterface $delegate;

    public function __construct(
        private readonly TranslatorFactory $factory
    ) {
    }

    public function setDelegate(TranslatorInterface&LocaleAwareInterface $delegate): void
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

    public function getLocale(): string
    {
        return $this->getDelegate()->getLocale();
    }

    public function setLocale(string $locale): void
    {
        $this->getDelegate()->setLocale($locale);
    }

    private function getDelegate(): TranslatorInterface&LocaleAwareInterface
    {
        if (!isset($this->delegate)) {
            $this->delegate = $this->factory->createBase();
        }

        return $this->delegate;
    }
}
