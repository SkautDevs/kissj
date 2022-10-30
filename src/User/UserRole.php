<?php

declare(strict_types=1);

namespace kissj\User;

enum UserRole: string
{

    case Participant = 'participant';
    case Admin = 'admin';
    case ContingentAdminCs = 'contingentAdminCs';
    case ContingentAdminSk = 'contingentAdminSk';
    case ContingentAdminPl = 'contingentAdminPl';
    case ContingentAdminHu = 'contingentAdminHu';
    case ContingentAdminEu = 'contingentAdminEu';
    case ContingentAdminRo = 'contingentAdminRo';

    /**
     * @return UserRole[]
     */
    public static function adminRoles(): array
    {
        return [
            self::Admin,
            self::ContingentAdminCs,
            self::ContingentAdminSk,
            self::ContingentAdminPl,
            self::ContingentAdminHu,
            self::ContingentAdminEu,
            self::ContingentAdminRo,
        ];
    }
}
