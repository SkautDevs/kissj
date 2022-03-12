<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddTroopInfosIntoEvent extends AbstractMigration
{
    public function up(): void
    {
        $event = $this->table('event');
        $event
            ->addColumn('maximalClosedTroopsCount', 'integer', ['default' => null])
            ->addColumn('minimalTroopParticipantsCount', 'integer', ['default' => null])
            ->addColumn('maximalTroopParticipantsCount', 'integer', ['default' => null])
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
