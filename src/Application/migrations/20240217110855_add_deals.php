<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddDeals extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('deal');
        $table->addColumn('slug', 'text', ['null' => false]);
        $table->addColumn('is_done', 'boolean', ['null' => false]);
        $table->addColumn('done_at', 'datetime', ['null' => true]);
        $table->addColumn('url_address', 'text', ['null' => false]);
        $table->addColumn('data', 'text', ['null' => false]);
        $table->addColumn('participant_id', 'integer', ['null' => false]);
        $table->addForeignKey('participant_id', 'participant', ['id'], ['constraint' => 'deal_participant_id_fk']);
        $table->addColumn('created_at', 'datetime', ['null' => false]);
        $table->addColumn('updated_at', 'datetime', ['null' => false]);
        $table->create();
    }

    public function down(): void
    {
        $table = $this->table('deal');
        $table->drop()->save();
    }
}
