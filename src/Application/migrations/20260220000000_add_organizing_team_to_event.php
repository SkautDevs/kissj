<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddOrganizingTeamToEvent extends AbstractMigration
{
    public function up(): void
    {
        $eventTable = $this->table('event');
        $eventTable->addColumn('allow_organizing_team', 'boolean', ['null' => false, 'default' => false]);
        $eventTable->addColumn('maximal_closed_organizing_team_count', 'integer', ['null' => true]);
        $eventTable->addColumn('organizing_team_price', 'integer', ['null' => true]);
        $eventTable->addColumn('organizing_team_registration_token', 'string', ['null' => true]);
        $eventTable->save();
    }

    public function down(): void
    {
        $eventTable = $this->table('event');
        $eventTable->removeColumn('allow_organizing_team');
        $eventTable->removeColumn('maximal_closed_organizing_team_count');
        $eventTable->removeColumn('organizing_team_price');
        $eventTable->removeColumn('organizing_team_registration_token');
        $eventTable->save();
    }
}
