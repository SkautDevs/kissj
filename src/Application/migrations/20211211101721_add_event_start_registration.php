<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddEventStartRegistration extends AbstractMigration
{
    public function up(): void
    {
        $event = $this->table('event');
        $event
            ->addColumn('start_registration', 'datetime', ['default' => '1970-01-01', 'null' => false])
            ->save();
    }

    public function down(): void
    {
        $event = $this->table('event');
        $event->removeColumn('start_registration')->save();
    }
}
