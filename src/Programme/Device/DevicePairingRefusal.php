<?php

declare(strict_types=1);

namespace kissj\Programme\Device;

enum DevicePairingRefusal
{
    case TooManyAttempts;
    case InvalidCode;
    case CodeAlreadyUsed;

    public function flashKey(): string
    {
        return match ($this) {
            self::TooManyAttempts => 'flash.warning.devicePairingTooManyAttempts',
            self::InvalidCode => 'flash.warning.devicePairingCodeInvalid',
            self::CodeAlreadyUsed => 'flash.warning.devicePairingCodeUsed',
        };
    }
}
