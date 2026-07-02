<?php

declare(strict_types=1);

namespace kissj\Participant;

enum Gender: string
{
    case Man = 'man';
    case Woman = 'woman';
    case Other = 'other';

    public static function fromSkautisIdSex(?string $idSex): self
    {
        return match ($idSex) {
            'male' => self::Man,
            'female' => self::Woman,
            default => self::Other,
        };
    }

    public function toEmailSuffix(): string
    {
        return match ($this) {
            self::Man => '.man',
            self::Woman => '.woman',
            self::Other => '',
        };
    }
}
