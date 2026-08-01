<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddAdminValues extends AbstractMigration
{
    public function up(): void
    {
        $tableParticipant = $this->table('participant');
        $tableParticipant->addColumn('subcamp', 'string', ['null' => true]);
        $tableParticipant->addColumn('internal_unique_id', 'string', ['null' => true]);
        $tableParticipant->addColumn('internal_common_id', 'string', ['null' => true]);
        $tableParticipant->save();
    }

    public function down(): void
    {
        $participantTable = $this->table('participant');
        $participantTable->removeColumn('subcamp');
        $participantTable->removeColumn('internal_unique_id');
        $participantTable->removeColumn('internal_common_id');
        $participantTable->save();
    }
}
