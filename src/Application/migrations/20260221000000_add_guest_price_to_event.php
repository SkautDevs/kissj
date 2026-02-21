<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddGuestPriceToEvent extends AbstractMigration
{
    public function up(): void
    {
        $eventTable = $this->table('event');
        $eventTable->addColumn('guest_price', 'integer', ['null' => true]);
        $eventTable->save();
    }

    public function down(): void
    {
        $eventTable = $this->table('event');
        $eventTable->removeColumn('guest_price');
        $eventTable->save();
    }
}
