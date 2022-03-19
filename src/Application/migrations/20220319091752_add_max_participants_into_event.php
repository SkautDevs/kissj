<?php declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddMaxParticipantsIntoEvent extends AbstractMigration
{
    public function up(): void
    {
        $event = $this->table('event');
        $event
            ->addColumn('maximal_closed_participants_count', 'integer', ['default' => null, 'null' => true])
            ->save();
    }

    public function down(): void
    {
        $event = $this->table('event');
        $event
            ->removeColumn('maximal_closed_participants_count')
            ->save();
    }
}
