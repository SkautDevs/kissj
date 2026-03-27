<?php

declare(strict_types=1);

namespace kissj\Participant;

enum Gender: string
{
    case Man = 'man';
    case Woman = 'woman';
    case Other = 'other';

    public static function fromSkautisDisplayName(?string $displayName): self
    {
        return match (($displayName !== null) ? mb_strtolower($displayName) : null) {
            'muž', 'male', 'man' => self::Man,
            'žena', 'female', 'woman' => self::Woman,
            default => self::Other,
        };
    }
}
