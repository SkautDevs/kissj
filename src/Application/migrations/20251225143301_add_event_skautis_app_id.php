<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddEventSkautisAppId extends AbstractMigration
{
    public function up(): void
    {
        $eventTable = $this->table('event');
        $eventTable->addColumn('skautis_app_id', 'string', ['null' => false, 'default' => '']);
        $eventTable->save();
    }

    public function down(): void
    {
        $eventTable = $this->table('event');
        $eventTable->removeColumn('skautis_app_id');
        $eventTable->save();
    }
}
