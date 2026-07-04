<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddProgrammeWindowToEvent extends AbstractMigration
{
    public function up(): void
    {
        $event = $this->table('event');
        $event->addColumn('programme_registration_start', 'datetime', ['null' => true]);
        $event->addColumn('programme_registration_end', 'datetime', ['null' => true]);
        $event->update();
    }

    public function down(): void
    {
        $event = $this->table('event');
        $event->removeColumn('programme_registration_start');
        $event->removeColumn('programme_registration_end');
        $event->update();
    }
}
