<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveBankBreakpoint extends AbstractMigration
{
    public function up(): void
    {
        $event = $this->table('event');
        $event->removeColumn('bank_breakpoint')->save();
    }

    public function down(): void
    {
        $event = $this->table('event');
        $event
            ->addColumn('bank_breakpoint', 'datetime', ['default' => 'NOW()', 'null' => false])
            ->save();
    }
}
