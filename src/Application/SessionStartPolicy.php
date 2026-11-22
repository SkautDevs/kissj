<?php

declare(strict_types=1);

namespace kissj\Application;

final class SessionStartPolicy
{
    // public images are cached by browsers and proxies, so their responses must carry no session cookie
    private const string SESSIONLESS_PATH_PATTERN = '~/v3/programme/image/[a-f0-9]{32}(?:/thumb)?/?$~D';

    public static function isSessionless(string $requestUri): bool
    {
        $path = parse_url($requestUri, PHP_URL_PATH);
        if (is_string($path) === false) {
            return false;
        }

        return preg_match(self::SESSIONLESS_PATH_PATTERN, $path) === 1;
    }
}
