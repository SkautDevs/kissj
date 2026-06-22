<?php

declare(strict_types=1);

namespace kissj\Telemetry\Sentry;

readonly class SqlParameterizer
{
    public const string REDACTED = '<sql-redacted>';

    public static function parameterize(string $sql): string
    {
        $patterns = [
            '/\$([A-Za-z_]\w*|)\$.*?\$\1\$/us',
            "/'[^']*(?:''[^']*)*'/u",
            '/(?<![\w$])\d+(?:\.\d+)?[eE][+-]?\d+(?!\w)/u',
            '/(?<![\w$])\d+(?:\.\d+)?(?!\w)/u',
        ];
        foreach ($patterns as $pattern) {
            $sql = preg_replace($pattern, '?', $sql);
            if ($sql === null) {
                return self::REDACTED;
            }
        }

        return $sql;
    }
}
