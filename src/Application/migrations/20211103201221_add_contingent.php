<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddContingent extends AbstractMigration
{
    public function up(): void
    {
        $event = $this->table('participant');
        $event
            ->addColumn('contingent', 'text', ['null' => true])
            ->save();
    }

    public function down(): void
    {
        $event = $this->table('participant');
        $event
            ->removeColumn('contingent')
            ->save();
    }
}
