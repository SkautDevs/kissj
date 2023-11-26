<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUserLoginTypeAndSkautisId extends AbstractMigration
{
    public function up(): void
    {
        $tableUser = $this->table('user');
        $tableUser->addColumn('skautis_id', 'integer', ['null' => true]);
        $tableUser->addColumn('skautis_has_membership', 'boolean', ['null' => true]);
        $tableUser->addColumn('login_type', 'string', ['null' => false, 'default' => 'email']);
        $tableUser->save();

        $tableUser->changeColumn('login_type', 'string', ['null' => false]);
        $tableUser->save();
    }

    public function down(): void
    {
        $tableUser = $this->table('user');
        $tableUser->removeColumn('skautis_id');
        $tableUser->removeColumn('skautis_has_membership');
        $tableUser->removeColumn('login_type');
        $tableUser->save();
    }
}
