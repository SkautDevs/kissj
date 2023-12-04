<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddParticipantAdminNote extends AbstractMigration
{
    public function up(): void
    {
        $event = $this->table('participant');
        $event
            ->addColumn('admin_note', 'string', ['default' => '', 'null' => false])
            ->save();
    }

    public function down(): void
    {
        $event = $this->table('participant');
        $event
            ->removeColumn('admin_note')
            ->save();
    }
}
