<?php

declare(strict_types=1);

namespace kissj\Programme\Device;

readonly class DevicePairingCodes
{
    /** 6 digits with leading zeros */
    public static function generateUserCode(): string
    {
        return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /** 32 lowercase hex characters */
    public static function generateDeviceCode(): string
    {
        return bin2hex(random_bytes(16));
    }

    /** 64 lowercase hex characters */
    public static function generateSessionToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public static function normaliseUserCode(string $entered): string
    {
        return str_replace([' ', '-'], '', $entered);
    }
}
