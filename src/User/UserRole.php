<?php

declare(strict_types=1);

namespace kissj\User;

enum UserRole: string
{
    case Participant = 'participant';
    case Admin = 'admin';
    case IstAdmin = 'istAdmin';
    case ContingentAdminCs = 'contingentAdminCs';
    case ContingentAdminSk = 'contingentAdminSk';
    case ContingentAdminPl = 'contingentAdminPl';
    case ContingentAdminHu = 'contingentAdminHu';
    case ContingentAdminEu = 'contingentAdminEu';
    case ContingentAdminRo = 'contingentAdminRo';
    case ContingentAdminGb = 'contingentAdminGb';
    case ContingentAdminSw = 'contingentAdminSw';

    /**
     * @return UserRole[]
     */
    public static function adminRoles(): array
    {
        return [
            self::Admin,
            self::IstAdmin,
            self::ContingentAdminCs,
            self::ContingentAdminSk,
            self::ContingentAdminPl,
            self::ContingentAdminHu,
            self::ContingentAdminEu,
            self::ContingentAdminRo,
            self::ContingentAdminGb,
            self::ContingentAdminSw,
        ];
    }

    public function isEligibleToHandlePayments(): bool
    {
        return match ($this) {
            self::Admin, self::ContingentAdminCs, self::ContingentAdminSk => true,
            default => false,
        };
    }

    public function isEligibleToManageTroops(): bool
    {
        return match ($this) {
            self::Admin, self::ContingentAdminCs, self::ContingentAdminSk => true,
            default => false,
        };
    }

    public function isEligibleToImportIst(): bool
    {
        return match ($this) {
            self::Admin => true,
            default => false,
        };
    }
}
