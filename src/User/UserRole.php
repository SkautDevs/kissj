<?php

declare(strict_types=1);

namespace kissj\User;

enum UserRole: string
{
    case Participant = 'participant';
    case Admin = 'admin';
    case IstAdmin = 'istAdmin';
    case ProgrammeAdmin = 'programmeAdmin';
    case ContingentAdminCs = 'contingentAdminCs';
    case ContingentAdminSk = 'contingentAdminSk';
    case ContingentAdminPl = 'contingentAdminPl';
    case ContingentAdminHu = 'contingentAdminHu';
    case ContingentAdminEu = 'contingentAdminEu';
    case ContingentAdminRo = 'contingentAdminRo';

    /**
     * @return list<UserRole>
     */
    public static function adminRoles(): array
    {
        return [self::Admin, self::IstAdmin, ...self::contingentAdminRoles()];
    }

    /**
     * @return list<UserRole>
     */
    public static function contingentAdminRoles(): array
    {
        return [
            self::ContingentAdminCs,
            self::ContingentAdminSk,
            self::ContingentAdminPl,
            self::ContingentAdminHu,
            self::ContingentAdminEu,
            self::ContingentAdminRo,
        ];
    }

    public function isEligibleToManageProgrammes(): bool
    {
        return match ($this) {
            self::Admin,
            self::ProgrammeAdmin
                => true,
            self::Participant,
            self::IstAdmin,
            self::ContingentAdminCs,
            self::ContingentAdminSk,
            self::ContingentAdminPl,
            self::ContingentAdminHu,
            self::ContingentAdminEu,
            self::ContingentAdminRo,
                => false,
        };
    }

}
