<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveElapsedDays extends AbstractMigration
{
    public function up(): void
    {
        $event = $this->table('event');
        $event
            ->removeColumn('max_elapsed_payment_days')
            ->save();
    }

    public function down(): void
    {
        $event = $this->table('event');
        $event
            ->addColumn('max_elapsed_payment_days', 'integer', ['null' => false])
            ->save();
    }
}
