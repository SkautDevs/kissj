<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddTechnicalTeamsToEvent extends AbstractMigration
{
    public function up(): void
    {
        $eventTable = $this->table('event');
        $eventTable->addColumn('allow_technical_teams', 'boolean', ['null' => false, 'default' => false]);
        $eventTable->addColumn('technical_team_price', 'integer', ['null' => true]);
        $eventTable->addColumn('technical_team_registration_token', 'string', ['null' => true]);
        $eventTable->save();
    }

    public function down(): void
    {
        $eventTable = $this->table('event');
        $eventTable->removeColumn('allow_technical_teams');
        $eventTable->removeColumn('technical_team_price');
        $eventTable->removeColumn('technical_team_registration_token');
        $eventTable->save();
    }
}
