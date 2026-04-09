<?php

declare(strict_types=1);

namespace kissj\Participant;

enum Country: string
{
    case CzechRepublic = 'detail.countryCzechRepublic';
    case Slovakia = 'detail.countrySlovakia';
    case Other = 'detail.countryOther';

    public static function fromSkautisState(?string $state): self
    {
        if ($state === null || $state === '') {
            return self::Other;
        }

        return match (mb_strtolower($state)) {
            'česká republika', 'czech republic', 'czechia' => self::CzechRepublic,
            'slovensko', 'slovakia', 'slovak republic' => self::Slovakia,
            default => self::Other,
        };
    }
}
