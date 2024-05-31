<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddLeaveDate extends AbstractMigration
{
    public function up(): void
    {
        $tableParticipant = $this->table('participant');
        $tableParticipant->addColumn('leave_date', 'datetime', ['null' => true]);
        $tableParticipant->save();
    }

    public function down(): void
    {
        $participantTable = $this->table('participant');
        $participantTable->removeColumn('leave_date');
        $participantTable->save();
    }
}
