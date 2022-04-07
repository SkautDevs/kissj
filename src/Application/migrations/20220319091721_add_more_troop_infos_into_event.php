<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddMoreTroopInfosIntoEvent extends AbstractMigration
{
    public function up(): void
    {
        $event = $this->table('event');
        $event
            ->removeColumn('maximalClosedTroopsCount')
            ->addColumn('maximalClosedTroopLeadersCount', 'integer', ['default' => null, 'null' => true])
            ->addColumn('maximalClosedTroopParticipantsCount', 'integer', ['default' => null, 'null' => true])
            ->save();
    }

    public function down(): void
    {
        $event = $this->table('event');
        $event
            ->removeColumn('maximalClosedTroopLeadersCount')
            ->removeColumn('maximalClosedTroopParticipantsCount')
            ->addColumn('maximalClosedTroopsCount', 'integer', ['default' => null, 'null' => true])
            ->save();
    }
}
