<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddTestingSiteFlag extends AbstractMigration
{
    public function up(): void
    {
        $event = $this->table('event');
        $event
            ->addColumn('testing_site', 'boolean', ['default' => false, 'null' => false])
            ->save();
    }

    public function down(): void
    {
        $event = $this->table('event');
        $event->removeColumn('testing_site')->save();
    }
}
