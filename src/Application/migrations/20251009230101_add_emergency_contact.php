<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddEmergencyContact extends AbstractMigration
{
    public function up(): void
    {
        $tableParticipant = $this->table('participant');
        $tableParticipant->addColumn('emergency_contact', 'string', ['null' => true]);
        $tableParticipant->save();
    }

    public function down(): void
    {
        $participantTable = $this->table('participant');
        $participantTable->removeColumn('emergency_contact');
        $participantTable->save();
    }
}
