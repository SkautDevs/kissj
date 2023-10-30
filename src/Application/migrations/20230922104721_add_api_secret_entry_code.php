<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddApiSecretEntryCode extends AbstractMigration
{
    public function up(): void
    {
        $tableParticipant = $this->table('participant');
        $tableParticipant->addColumn('entry_date', 'datetime', ['null' => true]);
        $tableParticipant->addColumn('entry_code', 'string', ['null' => false, 'default' => 'ABCDEFGH']);
        $tableParticipant->save();

        $tableParticipant->changeColumn('entry_code', 'string', ['null' => false]);
        $tableParticipant->save();


        $tableEvent = $this->table('event');
        $tableEvent->addColumn('api_secret', 'string', ['null' => false, 'default' => '']);
        $tableEvent->save();
    }

    public function down(): void
    {
        $participantTable = $this->table('participant');
        $participantTable->removeColumn('entry_date');
        $participantTable->removeColumn('entry_code');
        $participantTable->save();

        $eventTable = $this->table('event');
        $eventTable->removeColumn('api_secret');
        $eventTable->save();
    }
}
