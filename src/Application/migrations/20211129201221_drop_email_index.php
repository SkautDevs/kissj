<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DropEmailIndex extends AbstractMigration
{
    public function up(): void
    {
        $user = $this->table('user');
        $user
            ->removeIndexByName('user_email_uindex')
            ->save();
        $user
            ->addIndex('email')
            ->save();
    }

    public function down(): void
    {
        $user = $this->table('user');
        $user
            ->removeIndex('email')
            ->save();

        $user
            ->addIndex('email', [
                'name' => 'user_email_uindex',
                'unique' => true,
            ])
            ->save();
    }
}
