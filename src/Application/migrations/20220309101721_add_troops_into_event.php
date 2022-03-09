<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddTroopsIntoEvent extends AbstractMigration
{
    public function up(): void
    {
        $event = $this->table('event');
        $event
            ->addColumn('allow_troops', 'boolean', ['default' => false, 'null' => false])
            ->save();
    }

    public function down(): void
    {
        $event = $this->table('event');
        $event->removeColumn('allow_troops')->save();
    }
}
