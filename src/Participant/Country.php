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
        return match ($state) {
            'Česká republika' => self::CzechRepublic,
            'Slovensko' => self::Slovakia,
            default => self::Other,
        };
    }
}
