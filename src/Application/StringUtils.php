<?php

declare(strict_types=1);

namespace kissj\Application;

class StringUtils
{
    public static function stripDiacritic(string $text): string
    {
        $diacritic = [
            'ě', 'š', 'č', 'ř', 'ž', 'ý', 'á', 'í', 'é', 'ú', 'ů', 'ť', 'ď', 'ó', 'ä', 'ë', 'ü',
            'Ě', 'Š', 'Č', 'Ř', 'Ž', 'Ý', 'Á', 'Í', 'É', 'Ú', 'Ů', 'Ť', 'Ď', 'Ó', 'Ä', 'Ë', 'Ü',
        ];
        $without = [
            'e', 's', 'c', 'r', 'z', 'y', 'a', 'i', 'e', 'u', 'u', 't', 'd', 'ó', 'a', 'e', 'u',
            'E', 'S', 'C', 'R', 'Z', 'Y', 'A', 'I', 'E', 'U', 'U', 'T', 'D', 'Ó', 'A', 'E', 'U',
        ];

        return str_replace($diacritic, $without, $text);
    }

    public static function padWithZeroes(string $text, int $length): string
    {
        return str_pad($text, $length, '0', STR_PAD_LEFT);
    }
}
