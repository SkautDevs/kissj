<?php

declare(strict_types=1);

namespace kissj\Participant;

use LogicException;
use Transliterator;

enum Country: string
{
    case CzechRepublic = 'detail.countryCzechRepublic';
    case Slovakia = 'detail.countrySlovakia';
    case Other = 'detail.countryOther';

    public static function fromSkautisState(?string $state): self
    {
        $key = self::normalise($state ?? '');

        return match (true) {
            in_array($key, ['cr', 'cz', 'cze'], true),
            str_starts_with($key, 'cesk'),
            str_starts_with($key, 'czech') => self::CzechRepublic,
            in_array($key, ['sk', 'sr', 'svk'], true),
            str_starts_with($key, 'slovensk'),
            str_starts_with($key, 'slovak') => self::Slovakia,
            default => self::Other,
        };
    }

    private static function normalise(string $state): string
    {
        $transliterator = Transliterator::create('Any-Latin; Latin-ASCII; Lower()');
        if ($transliterator === null) {
            throw new LogicException('Transliterator is not available');
        }

        $latin = $transliterator->transliterate($state);
        if ($latin === false) {
            throw new LogicException('Transliteration of state failed');
        }

        $key = preg_replace('/[^a-z]/', '', $latin);
        if ($key === null) {
            throw new LogicException('Normalising state failed');
        }

        return $key;
    }
}
