<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddPrintedHandbookSwitch extends AbstractMigration
{
    public function up(): void
    {
        $tableUser = $this->table('participant');
        $tableUser->addColumn('printed_handbook', 'string', ['null' => true]);
        $tableUser->save();
    }

    public function down(): void
    {
        $tableUser = $this->table('participant');
        $tableUser->removeColumn('printed_handbook');
        $tableUser->save();
    }
}
