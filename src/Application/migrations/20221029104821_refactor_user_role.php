<?php

declare(strict_types=1);

use kissj\User\UserRole;
use Phinx\Migration\AbstractMigration;

final class RefactorUserRole extends AbstractMigration
{
    public function up(): void
    {
        $adminRoles = implode(',', array_map(function (UserRole $role): string {
            return "'" . $role->value . "'";
        }, UserRole::adminRoles()));

        $this->execute("UPDATE public.user SET role = 'participant' WHERE role NOT IN (" . $adminRoles . ")");
    }

    public function down(): void
    {
        // non-reversible data change
    }
}
