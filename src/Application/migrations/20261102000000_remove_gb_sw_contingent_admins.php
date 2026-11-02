<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class RemoveGbSwContingentAdmins extends AbstractMigration
{
    public function up(): void
    {
        $this->execute(
            'UPDATE "user" SET role = \'participant\' WHERE role IN (\'contingentAdminGb\', \'contingentAdminSw\')'
        );
    }

    public function down(): void
    {
    }
}
