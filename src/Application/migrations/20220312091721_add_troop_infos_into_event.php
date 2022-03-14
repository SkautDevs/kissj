<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddTroopInfosIntoEvent extends AbstractMigration
{
    public function up(): void
    {
        $event = $this->table('event');
        $event
            ->addColumn('maximalClosedTroopsCount', 'integer', ['default' => null, 'null' => true])
            ->addColumn('minimalTroopParticipantsCount', 'integer', ['default' => null, 'null' => true])
            ->addColumn('maximalTroopParticipantsCount', 'integer', ['default' => null, 'null' => true])
            ->save();
    }

    public function down(): void
    {
        $event = $this->table('event');
        $event
            ->removeColumn('maximalClosedTroopsCount')
            ->removeColumn('minimalTroopParticipantsCount')
            ->removeColumn('maximalTroopParticipantsCount')
            ->save();
    }
}
