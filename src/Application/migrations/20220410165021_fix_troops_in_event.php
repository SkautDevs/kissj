<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class FixTroopsInEvent extends AbstractMigration
{
    public function up(): void
    {
        $event = $this->table('event');
        $event
            ->addColumn('minimal_troop_participants_count', 'integer', ['default' => null, 'null' => true])
            ->addColumn('maximal_troop_participants_count', 'integer', ['default' => null, 'null' => true])
            ->addColumn('maximal_closed_troop_leaders_count', 'integer', ['default' => null, 'null' => true])
            ->addColumn('maximal_closed_troop_participants_count', 'integer', ['default' => null, 'null' => true])
            ->save();

        $event
            ->removeColumn('minimalTroopParticipantsCount')
            ->removeColumn('maximalTroopParticipantsCount')
            ->removeColumn('maximalClosedTroopLeadersCount')
            ->removeColumn('maximalClosedTroopParticipantsCount')
            ->save();
    }

    public function down(): void
    {
        $event = $this->table('event');
        $event
            ->addColumn('minimalTroopParticipantsCount', 'integer', ['default' => null, 'null' => true])
            ->addColumn('maximalTroopParticipantsCount', 'integer', ['default' => null, 'null' => true])
            ->addColumn('maximalClosedTroopLeadersCount', 'integer', ['default' => null, 'null' => true])
            ->addColumn('maximalClosedTroopParticipantsCount', 'integer', ['default' => null, 'null' => true])
            ->save();

        $event
            ->removeColumn('minimal_troop_participants_count')
            ->removeColumn('maximal_troop_participants_count')
            ->removeColumn('maximal_closed_troop_leaders_count')
            ->removeColumn('maximal_closed_troop_participants_count')
            ->save();
    }
}
