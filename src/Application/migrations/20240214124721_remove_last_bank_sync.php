<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveLastBankSync extends AbstractMigration
{
    public function up(): void
    {
        $event = $this->table('event');
        $event->removeColumn('last_bank_sync')->save();
    }

    public function down(): void
    {
        $event = $this->table('event');
        $event->addColumn('last_bank_sync', 'datetime', ['default' => 'NOW()', 'null' => false]);
        $event->save();
    }
}
