<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class AddApiKeyEventToEvent extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('event');
        $table->addColumn('api_key_event', 'string', ['null' => true, 'default' => null]);
        $table->addIndex(['api_key_event'], ['unique' => true, 'name' => 'event_api_key_event_unique']);
        $table->save();
    }

    public function down(): void
    {
        $table = $this->table('event');
        $table->removeIndexByName('event_api_key_event_unique');
        $table->removeColumn('api_key_event');
        $table->save();
    }
}
