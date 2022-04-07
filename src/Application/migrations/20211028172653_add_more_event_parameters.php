<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddMoreEventParameters extends AbstractMigration
{
    public function up(): void
    {
        $event = $this->table('event');
        $event->addColumn('default_price', 'integer', ['default' => '0'])
			->addColumn('event_type', 'text', ['default' => 'default'])
			->addColumn('logo_url', 'text', ['default' => ''])
			->save();
    }

    public function down(): void
    {
        $event = $this->table('event');
        $event->removeColumn('default_price')
			->removeColumn('event_type')
			->removeColumn('logo_url')
			->save();
    }
}
