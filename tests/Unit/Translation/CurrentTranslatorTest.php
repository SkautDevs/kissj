<?php

declare(strict_types=1);

namespace Tests\Unit\Translation;

use kissj\Translation\CurrentTranslator;
use kissj\Translation\TranslatorFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CurrentTranslatorTest extends TestCase
{
    public function testForwardsTransToDelegate(): void
    {
        $delegate = new RecordingTranslator(['key.one' => 'hello']);
        $translator = $this->makeTranslator($delegate);

        self::assertSame('hello', $translator->trans('key.one'));
        self::assertSame([['key.one', [], null, null]], $delegate->calls);
    }

    public function testForwardsParametersDomainAndLocale(): void
    {
        $delegate = new RecordingTranslator(['greet' => 'hi %name%']);
        $translator = $this->makeTranslator($delegate);

        $translator->trans('greet', ['%name%' => 'Lung'], 'messages', 'en');

        self::assertSame([['greet', ['%name%' => 'Lung'], 'messages', 'en']], $delegate->calls);
    }

    public function testGetLocaleDelegates(): void
    {
        $delegate = new RecordingTranslator([], 'cs');
        $translator = $this->makeTranslator($delegate);

        self::assertSame('cs', $translator->getLocale());
    }

    public function testSetLocaleDelegates(): void
    {
        $delegate = new RecordingTranslator([], 'cs');
        $translator = $this->makeTranslator($delegate);

        $translator->setLocale('sk');

        self::assertSame('sk', $delegate->getLocale());
    }

    public function testSetDelegateSwapsTarget(): void
    {
        $first = new RecordingTranslator(['key' => 'one']);
        $second = new RecordingTranslator(['key' => 'two']);
        $translator = $this->makeTranslator($first);

        self::assertSame('one', $translator->trans('key'));

        $translator->setDelegate($second);

        self::assertSame('two', $translator->trans('key'));
    }

    public function testLazilyFallsBackToFactoryBase(): void
    {
        $factory = new TranslatorFactory('cs', null, false);
        $translator = new CurrentTranslator($factory);

        // No setDelegate() call - factory must materialize base on first use.
        self::assertSame('Odeslat registraci', $translator->trans('closeRegistration.title'));
    }

    public function testTransGenderedUsesGenderedVariantDefinedInCurrentLocale(): void
    {
        $translator = new CurrentTranslator(new TranslatorFactory('cs', null, false));
        $translator->setLocale('cs');

        self::assertSame(
            'Pokud máš otázky, mohl bys odpovědi na ně najít na našem webu',
            $translator->transGendered('email.layout.ifQuestion', '.man'),
        );
    }

    public function testTransGenderedIgnoresGenderedVariantFromFallbackLocale(): void
    {
        $translator = new CurrentTranslator(new TranslatorFactory('cs', null, false));
        $translator->setLocale('en');

        self::assertSame(
            'If you have any questions, you may seek the answer at our webpage',
            $translator->transGendered('email.layout.ifQuestion', '.man'),
        );
    }

    public function testTransGenderedWithEmptySuffixTranslatesNeutralKey(): void
    {
        $translator = new CurrentTranslator(new TranslatorFactory('cs', null, false));
        $translator->setLocale('cs');

        self::assertSame(
            'Pokud máš otázky, můžeš odpovědi na ně najít na našem webu',
            $translator->transGendered('email.layout.ifQuestion', ''),
        );
    }

    private function makeTranslator(
        TranslatorInterface&LocaleAwareInterface&TranslatorBagInterface $delegate,
    ): CurrentTranslator {
        $translator = new CurrentTranslator(new TranslatorFactory('cs', null, false));
        $translator->setDelegate($delegate);

        return $translator;
    }
}

class RecordingTranslator implements TranslatorInterface, LocaleAwareInterface, TranslatorBagInterface
{
    /** @var list<array{string, array<array-key, mixed>, ?string, ?string}> */
    public array $calls = [];

    /**
     * @param array<string, string> $translations
     */
    public function __construct(
        private readonly array $translations,
        private string $locale = 'en',
    ) {
    }

    /**
     * @param array<array-key, mixed> $parameters
     */
    public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        $this->calls[] = [$id, $parameters, $domain, $locale];

        return $this->translations[$id] ?? $id;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getCatalogue(?string $locale = null): MessageCatalogueInterface
    {
        return new MessageCatalogue($locale ?? $this->locale, ['messages' => $this->translations]);
    }

    /**
     * @return list<MessageCatalogueInterface>
     */
    public function getCatalogues(): array
    {
        return [$this->getCatalogue()];
    }
}
